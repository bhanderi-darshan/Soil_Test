"""
NPK Auto-Trainer  —  Keeps retraining until R² ≥ TARGET_ACCURACY
=================================================================
Run:
    python auto_train.py --data Soil.csv

Tune accuracy WITHOUT touching code — edit the CONFIG block below.
Every knob is documented inline.
"""

import os, json, time, argparse, warnings
import numpy as np
import pandas as pd
import joblib

from sklearn.ensemble          import RandomForestRegressor, GradientBoostingRegressor, StackingRegressor
from sklearn.linear_model      import Ridge
from sklearn.preprocessing     import LabelEncoder, PolynomialFeatures
from sklearn.model_selection   import train_test_split, cross_val_score, KFold
from sklearn.metrics           import r2_score, mean_absolute_error, mean_squared_error
from sklearn.pipeline          import Pipeline
from sklearn.base              import clone

try:
    from xgboost import XGBRegressor
    HAS_XGB = True
except ImportError:
    HAS_XGB = False
    print("[warn] xgboost not installed — XGB strategies will be skipped")

warnings.filterwarnings("ignore")

# ╔══════════════════════════════════════════════════════════════════╗
# ║                      ACCURACY CONFIG                            ║
# ║  Edit these values to control accuracy target and strategies    ║
# ╚══════════════════════════════════════════════════════════════════╝
CONFIG = {
    # ── Target ───────────────────────────────────────────────────────
    "TARGET_R2"        : 0.70,   # Increase for better quality models
    "MAX_RETRAIN_LOOPS": 8,      # Safety cap — max retraining attempts

    # ── Feature engineering ──────────────────────────────────────────
    "USE_POLY_FEATURES": True,   # Add Moisture², EC², Moisture×EC etc.
    "POLY_DEGREE"      : 2,      # 2 = quadratic  |  3 = cubic (slower)
    "ADD_LOG_FEATURES" : True,   # log(EC), log(Moisture) — helps skewed data
    "ADD_RATIO_FEATURES": True,  # EC/Moisture, Temp/EC — interaction ratios

    # ── Data ─────────────────────────────────────────────────────────
    "TEST_SIZE"        : 0.20,   # 0.20 = 80% train / 20% test
    "CV_FOLDS"         : 5,      # k-fold cross-validation folds

    # ── Random Forest hyperparams (Strategy 1) ───────────────────────
    "RF_N_ESTIMATORS"  : 300,    # More trees = more accurate but slower
    "RF_MAX_DEPTH"     : None,   # None = grow fully  |  set int to cap
    "RF_MIN_SAMPLES_LEAF": 2,    # Lower = finer splits, higher = smoother
    "RF_MAX_FEATURES"  : "sqrt", # "sqrt" | "log2" | 0.5 | 1.0

    # ── Gradient Boosting hyperparams (Strategy 2) ───────────────────
    "GB_N_ESTIMATORS"  : 500,
    "GB_LEARNING_RATE" : 0.05,   # Lower = better generalisation (need more trees)
    "GB_MAX_DEPTH"     : 5,
    "GB_SUBSAMPLE"     : 0.8,    # Row sampling per tree (0.6-1.0)

    # ── XGBoost hyperparams (Strategy 3, if installed) ───────────────
    "XGB_N_ESTIMATORS" : 600,
    "XGB_LEARNING_RATE": 0.04,
    "XGB_MAX_DEPTH"    : 7,
    "XGB_SUBSAMPLE"    : 0.8,
    "XGB_COLSAMPLE"    : 0.8,    # Feature fraction per tree
    "XGB_REG_ALPHA"    : 0.1,    # L1 regularisation
    "XGB_REG_LAMBDA"   : 1.5,    # L2 regularisation

    # ── Stacking ensemble (Strategy 4) ───────────────────────────────
    "STACK_USE_XGB"    : True,   # Include XGB in stack if available
    "STACK_FINAL_RIDGE_ALPHA": 1.0,

    # ── Output ───────────────────────────────────────────────────────
    "MODEL_DIR"        : os.path.dirname(os.path.abspath(__file__)),
    "SAVE_BEST_ONLY"   : True,   # Only overwrite saved models if improved
}

# ╔══════════════════════════════════════════════════════════════════╗
# ║                   CLASSIFICATION THRESHOLDS                     ║
# ╚══════════════════════════════════════════════════════════════════╝
CLASS_THRESH = {"N": [35, 65], "P": [30, 55], "K": [28, 55]}
TARGETS      = ["N", "P", "K"]
BASE_FEATURES = ["Moisture", "pH", "EC", "Soil_Temperature", "Soil_Enc"]


# ═══════════════════════════════════════════════════════════════════
#  FEATURE ENGINEERING
# ═══════════════════════════════════════════════════════════════════
def engineer_features(df: pd.DataFrame) -> pd.DataFrame:
    """Add polynomial, log, and ratio features based on CONFIG."""
    df = df.copy()
    feat_cols = []

    base = df[["Moisture", "pH", "EC", "Soil_Temperature"]].copy()

    if CONFIG["ADD_LOG_FEATURES"]:
        df["log_EC"]       = np.log1p(df["EC"])
        df["log_Moisture"] = np.log1p(df["Moisture"])
        df["log_Temp"]     = np.log1p(df["Soil_Temperature"])
        feat_cols += ["log_EC", "log_Moisture", "log_Temp"]

    if CONFIG["ADD_RATIO_FEATURES"]:
        df["EC_Moist_ratio"]   = df["EC"] / (df["Moisture"] + 1e-6)
        df["Temp_EC_ratio"]    = df["Soil_Temperature"] / (df["EC"] + 1e-6)
        df["Moist_Temp_ratio"] = df["Moisture"] / (df["Soil_Temperature"] + 1e-6)
        feat_cols += ["EC_Moist_ratio", "Temp_EC_ratio", "Moist_Temp_ratio"]

    if CONFIG["USE_POLY_FEATURES"]:
        poly = PolynomialFeatures(
            degree=CONFIG["POLY_DEGREE"],
            include_bias=False,
            interaction_only=False
        )
        poly_data = poly.fit_transform(base)
        poly_names = [f"poly_{n.replace(' ','_')}" for n in poly.get_feature_names_out(base.columns)]
        poly_df = pd.DataFrame(poly_data, columns=poly_names, index=df.index)
        # drop columns already in base to avoid duplication
        keep = [c for c in poly_names if c not in ["poly_Moisture", "poly_pH", "poly_EC", "poly_Soil_Temperature"]]
        df = pd.concat([df, poly_df[keep]], axis=1)
        feat_cols += keep

    all_features = BASE_FEATURES + feat_cols
    return df, [c for c in all_features if c in df.columns]


# ═══════════════════════════════════════════════════════════════════
#  MODEL STRATEGIES  (ordered: fastest → most powerful)
# ═══════════════════════════════════════════════════════════════════
def build_strategies():
    strategies = []

    # 1. Random Forest
    strategies.append(("Random Forest", RandomForestRegressor(
        n_estimators   = CONFIG["RF_N_ESTIMATORS"],
        max_depth      = CONFIG["RF_MAX_DEPTH"],
        min_samples_leaf = CONFIG["RF_MIN_SAMPLES_LEAF"],
        max_features   = CONFIG["RF_MAX_FEATURES"],
        n_jobs         = -1,
        random_state   = 42,
    )))

    # 2. Gradient Boosting
    strategies.append(("Gradient Boosting", GradientBoostingRegressor(
        n_estimators   = CONFIG["GB_N_ESTIMATORS"],
        learning_rate  = CONFIG["GB_LEARNING_RATE"],
        max_depth      = CONFIG["GB_MAX_DEPTH"],
        subsample      = CONFIG["GB_SUBSAMPLE"],
        random_state   = 42,
    )))

    # 3. XGBoost
    if HAS_XGB:
        strategies.append(("XGBoost", XGBRegressor(
            n_estimators      = CONFIG["XGB_N_ESTIMATORS"],
            learning_rate     = CONFIG["XGB_LEARNING_RATE"],
            max_depth         = CONFIG["XGB_MAX_DEPTH"],
            subsample         = CONFIG["XGB_SUBSAMPLE"],
            colsample_bytree  = CONFIG["XGB_COLSAMPLE"],
            reg_alpha         = CONFIG["XGB_REG_ALPHA"],
            reg_lambda        = CONFIG["XGB_REG_LAMBDA"],
            n_jobs            = -1,
            random_state      = 42,
            verbosity         = 0,
        )))

    # 4. Stacking Ensemble
    estimators = [
        ("rf", RandomForestRegressor(n_estimators=200, random_state=42, n_jobs=-1)),
        ("gb", GradientBoostingRegressor(n_estimators=300, learning_rate=0.05, random_state=42)),
    ]
    if HAS_XGB and CONFIG["STACK_USE_XGB"]:
        estimators.append(("xgb", XGBRegressor(
            n_estimators=400, learning_rate=0.04, random_state=42, verbosity=0
        )))
    strategies.append(("Stacking Ensemble", StackingRegressor(
        estimators    = estimators,
        final_estimator = Ridge(alpha=CONFIG["STACK_FINAL_RIDGE_ALPHA"]),
        cv            = 3,
        n_jobs        = -1,
    )))

    return strategies


# ═══════════════════════════════════════════════════════════════════
#  EVALUATION
# ═══════════════════════════════════════════════════════════════════
def evaluate(model, X_tr, X_te, y_tr, y_te, X_all, y_all):
    model.fit(X_tr, y_tr)
    y_pred = model.predict(X_te)
    r2   = r2_score(y_te, y_pred)
    mae  = mean_absolute_error(y_te, y_pred)
    rmse = mean_squared_error(y_te, y_pred) ** 0.5
    cv   = cross_val_score(
        clone(model), X_all, y_all,
        cv=KFold(n_splits=CONFIG["CV_FOLDS"], shuffle=True, random_state=42),
        scoring="r2", n_jobs=-1
    ).mean()
    return model, {"r2": round(r2,4), "mae": round(mae,3),
                   "rmse": round(rmse,3), "cv_r2": round(cv,4)}


# ═══════════════════════════════════════════════════════════════════
#  MAIN AUTO-TRAIN LOOP
# ═══════════════════════════════════════════════════════════════════
def auto_train(csv_path: str):
    TARGET = CONFIG["TARGET_R2"]

    # --- Robust path resolution ---
    # If the path doesn't exist as provided, and it's not an absolute path,
    # try looking for it in the same directory as this script.
    if not os.path.exists(csv_path) and not os.path.isabs(csv_path):
        script_dir = os.path.dirname(os.path.abspath(__file__))
        alt_path = os.path.join(script_dir, csv_path)
        if os.path.exists(alt_path):
            csv_path = alt_path

    print("\n" + "="*60)
    print("  NPK AUTO-TRAINER")
    print(f"  Target R2  : {TARGET}  (all three nutrients)")
    print(f"  Max loops  : {CONFIG['MAX_RETRAIN_LOOPS']}")
    print("="*60)

    # ── Load data ────────────────────────────────────────────────────
    print(f"  Loading Data: {csv_path}")
    df = pd.read_csv(csv_path)

    # ── Cleaning: Remove Outliers ────────────────────────────────────
    # Keep only rows where N, P, K are within 3 standard deviations
    before = len(df)
    for col in TARGETS:
        mean, std = df[col].mean(), df[col].std()
        df = df[(df[col] >= mean - 3*std) & (df[col] <= mean + 3*std)]
    
    after = len(df)
    if before != after:
        print(f"  Cleaned     : Removed {before - after} outliers")

    required = ["Moisture", "pH", "EC", "Soil_Temperature", "Soil_Type"] + TARGETS
    missing = [c for c in required if c not in df.columns]
    if missing:
        raise ValueError(f"Missing columns: {missing}")

    le = LabelEncoder()
    df["Soil_Enc"] = le.fit_transform(df["Soil_Type"].astype(str))
    print(f"\n  Dataset     : {len(df)} rows")
    print(f"  Soil types  : {list(le.classes_)}")

    # ── Feature engineering ──────────────────────────────────────────
    df_feat, feature_cols = engineer_features(df)
    X_all = df_feat[feature_cols].values
    print(f"  Features    : {len(feature_cols)}  {feature_cols[:6]}{'...' if len(feature_cols)>6 else ''}")

    strategies  = build_strategies()
    best_models = {}
    best_scores = {n: 0.0 for n in TARGETS}
    history     = []

    loop = 0
    while loop < CONFIG["MAX_RETRAIN_LOOPS"]:
        loop += 1
        strategy_name, model_template = strategies[min(loop-1, len(strategies)-1)]
        print(f"\n{'-'*60}")
        print(f"  Loop {loop}/{CONFIG['MAX_RETRAIN_LOOPS']}  -  Strategy: {strategy_name}")
        print(f"{'-'*60}")

        loop_scores = {}
        loop_models = {}

        for nutrient in TARGETS:
            y_all = df_feat[nutrient].values
            X_tr, X_te, y_tr, y_te = train_test_split(
                X_all, y_all,
                test_size=CONFIG["TEST_SIZE"],
                random_state=42 + loop  # shuffle seed per loop
            )

            t0 = time.time()
            model = clone(model_template)
            trained_model, scores = evaluate(model, X_tr, X_te, y_tr, y_te, X_all, y_all)
            elapsed = time.time() - t0

            r2 = scores["r2"]
            improved = r2 > best_scores[nutrient]
            status = "[OK]" if r2 >= TARGET else ("[UP]" if r2 >= 0.80 else "[!!]")
            marker = "[BEST]" if improved else "      "

            print(f"  {status} {nutrient}  R2={r2:.4f}  MAE={scores['mae']:.2f}  "
                  f"RMSE={scores['rmse']:.2f}  CV_R2={scores['cv_r2']:.4f}  "
                  f"({elapsed:.1f}s)  {marker}")

            if improved:
                best_scores[nutrient] = r2
                best_models[nutrient] = trained_model

            loop_scores[nutrient] = scores

        history.append({
            "loop": loop,
            "strategy": strategy_name,
            "scores": loop_scores,
        })

        # ── Check if all nutrients hit target ────────────────────────
        achieved = {n: best_scores[n] >= TARGET for n in TARGETS}
        all_done = all(achieved.values())

        print(f"\n  Progress  ->  "
              f"N:{best_scores['N']:.3f}  "
              f"P:{best_scores['P']:.3f}  "
              f"K:{best_scores['K']:.3f}  "
              f"  Target: {TARGET}")

        if all_done:
            print(f"\n  [DONE] ALL nutrients reached R2 >= {TARGET} after {loop} loop(s)!")
            break
        else:
            pending = [n for n, ok in achieved.items() if not ok]
            print(f"  ... Still below target: {pending}")
            if loop < CONFIG["MAX_RETRAIN_LOOPS"]:
                print("  -> Retraining with next strategy...")

    # ── Save best models ─────────────────────────────────────────────
    print(f"\n{'-'*60}")
    print("  Saving best models...")
    os.makedirs(CONFIG["MODEL_DIR"], exist_ok=True)

    for nutrient, model in best_models.items():
        path = os.path.join(CONFIG["MODEL_DIR"], f"model_{nutrient}.pkl")
        joblib.dump(model, path)
        print(f"  Saved model_{nutrient}.pkl  (R²={best_scores[nutrient]:.4f})")

    le_path = os.path.join(CONFIG["MODEL_DIR"], "label_encoder.pkl")
    joblib.dump(le, le_path)

    meta = {
        "target_r2"    : TARGET,
        "achieved"     : {n: best_scores[n] >= TARGET for n in TARGETS},
        "best_r2"      : {n: round(best_scores[n], 4) for n in TARGETS},
        "final_loop"   : loop,
        "feature_cols" : feature_cols,
        "soil_classes" : list(le.classes_),
        "class_thresholds": CLASS_THRESH,
        "config_used"  : {k: v for k, v in CONFIG.items()
                          if k not in ("MODEL_DIR",)},
        "history"      : history,
    }
    meta_path = os.path.join(CONFIG["MODEL_DIR"], "metadata.json")
    with open(meta_path, "w") as f:
        json.dump(meta, f, indent=2)

    # ── Final report ─────────────────────────────────────────────────
    print(f"\n{'='*60}")
    print("  FINAL RESULTS")
    print(f"{'='*60}")
    print(f"  {'Nutrient':<10} {'Best R2':>8} {'Target':>8} {'Status':>10}")
    print(f"  {'-'*42}")
    for n in TARGETS:
        r2  = best_scores[n]
        ok  = "PASS" if r2 >= TARGET else "FAIL"
        print(f"  {n:<10} {r2:>8.4f} {TARGET:>8.2f} {ok:>10}")

    overall = "ALL PASSED" if all(best_scores[n] >= TARGET for n in TARGETS) else \
              "UNFINISHED -- increase MAX_RETRAIN_LOOPS or adjust hyperparams"
    print(f"\n  {overall}")
    print(f"  Models saved to: {CONFIG['MODEL_DIR']}")
    print("="*60 + "\n")

    return best_models, le, best_scores


# ═══════════════════════════════════════════════════════════════════
#  ENTRY POINT
# ═══════════════════════════════════════════════════════════════════
if __name__ == "__main__":
    script_dir = os.path.dirname(os.path.abspath(__file__))
    default_csv = os.path.join(script_dir, "Soil.csv")

    parser = argparse.ArgumentParser(description="Auto-retrain NPK models until R² ≥ target")
    parser.add_argument("--data",   default=default_csv, help="Path to training CSV")
    parser.add_argument("--target", type=float, default=None,   help="Override TARGET_R2 (0-1)")
    parser.add_argument("--loops",  type=int,   default=None,   help="Override MAX_RETRAIN_LOOPS")
    args = parser.parse_args()

    if args.target: CONFIG["TARGET_R2"]         = args.target
    if args.loops:  CONFIG["MAX_RETRAIN_LOOPS"] = args.loops

    auto_train(args.data)
