"""
NPK Soil Nutrient Prediction Model
===================================
Train Random Forest models to predict N, P, K values from:
  - Moisture (%)
  - EC (dS/m)
  - Soil Temperature (°C)
  - Soil Type (Clay / Loamy / Sandy / Peaty / Silty)

Usage:
  python train_model.py --data your_dataset.csv
"""

import argparse
import json
import os
import numpy as np
import pandas as pd
import joblib
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import mean_absolute_error, r2_score, mean_squared_error


# ─── CONFIG ──────────────────────────────────────────────────────────────────
FEATURES       = ["Moisture", "EC", "Soil_Temperature", "Soil_Type"]
TARGETS        = ["N", "P", "K"]
CLASS_THRESH   = {"N": [35, 65], "P": [30, 55], "K": [28, 55]}
MODEL_DIR      = os.path.dirname(os.path.abspath(__file__))
RF_PARAMS      = dict(n_estimators=150, max_depth=12,
                      min_samples_leaf=4, random_state=42, n_jobs=-1)


# ─── HELPERS ─────────────────────────────────────────────────────────────────
def classify_nutrient(value: float, nutrient: str) -> str:
    lo, hi = CLASS_THRESH[nutrient]
    if value < lo:
        return "Low"
    elif value < hi:
        return "Medium"
    return "High"


def load_and_prepare(csv_path: str):
    df = pd.read_csv(csv_path)
    required = FEATURES + TARGETS
    missing = [c for c in required if c not in df.columns]
    if missing:
        raise ValueError(f"Missing columns in dataset: {missing}")

    le = LabelEncoder()
    df["Soil_Enc"] = le.fit_transform(df["Soil_Type"].astype(str))

    X = df[["Moisture", "EC", "Soil_Temperature", "Soil_Enc"]].values
    y = {t: df[t].values for t in TARGETS}
    return X, y, le


# ─── TRAINING ────────────────────────────────────────────────────────────────
def train(csv_path: str):
    print(f"\n[1/4] Loading dataset: {csv_path}")
    X, y, le = load_and_prepare(csv_path)
    print(f"      Rows: {len(X)}  |  Soil classes: {list(le.classes_)}")

    metrics  = {}
    models   = {}

    print("\n[2/4] Training models...")
    for nutrient in TARGETS:
        X_tr, X_te, y_tr, y_te = train_test_split(
            X, y[nutrient], test_size=0.2, random_state=42
        )
        model = RandomForestRegressor(**RF_PARAMS)
        model.fit(X_tr, y_tr)
        y_pred = model.predict(X_te)

        r2   = r2_score(y_te, y_pred)
        mae  = mean_absolute_error(y_te, y_pred)
        rmse = mean_squared_error(y_te, y_pred) ** 0.5
        cv   = cross_val_score(model, X, y[nutrient], cv=5, scoring="r2").mean()

        feat_names = ["Moisture", "EC", "Temperature", "Soil_Type"]
        fi = dict(zip(feat_names, [round(v, 3) for v in model.feature_importances_]))

        metrics[nutrient] = {
            "r2": round(r2, 4),
            "mae": round(mae, 3),
            "rmse": round(rmse, 3),
            "cv_r2": round(cv, 4),
            "feature_importance": fi,
        }
        models[nutrient] = model

        print(f"      {nutrient}  →  R²={r2:.3f}  MAE={mae:.2f}  RMSE={rmse:.2f}  CV_R²={cv:.3f}")
        print(f"           Feature importance: {fi}")

    print("\n[3/4] Saving models...")
    for nutrient, model in models.items():
        path = os.path.join(MODEL_DIR, f"model_{nutrient}.pkl")
        joblib.dump(model, path)
        print(f"      Saved {path}")

    le_path = os.path.join(MODEL_DIR, "label_encoder.pkl")
    joblib.dump(le, le_path)
    print(f"      Saved {le_path}")

    meta = {
        "label_encoder_classes": list(le.classes_),
        "features": ["Moisture", "EC", "Soil_Temperature", "Soil_Enc"],
        "feature_names": ["Moisture (%)", "EC (dS/m)", "Temperature (°C)", "Soil_Type (encoded)"],
        "metrics": metrics,
        "model_type": "RandomForestRegressor",
        "class_thresholds": CLASS_THRESH,
        "rf_params": RF_PARAMS,
    }
    meta_path = os.path.join(MODEL_DIR, "metadata.json")
    with open(meta_path, "w") as f:
        json.dump(meta, f, indent=2)
    print(f"      Saved {meta_path}")

    print("\n[4/4] Training complete!\n")
    print("  Metric Summary:")
    print(f"  {'Nutrient':<10} {'R²':>8} {'MAE':>8} {'RMSE':>8} {'CV_R²':>8}")
    print(f"  {'-'*44}")
    for nut, m in metrics.items():
        print(f"  {nut:<10} {m['r2']:>8.3f} {m['mae']:>8.2f} {m['rmse']:>8.2f} {m['cv_r2']:>8.3f}")
    print()

    return models, le, metrics


# ─── ENTRY POINT ─────────────────────────────────────────────────────────────
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Train NPK prediction models")
    parser.add_argument("--data", default="Soil.csv",
                        help="Path to training CSV file")
    args = parser.parse_args()
    train(args.data)
