"""
NPK Predictor  —  works with auto_train.py engineered features
================================================================
from predictor import NPKPredictor
m = NPKPredictor()
r = m.predict(moisture=37.9, ph=6.65, ec=1.77, temperature=28.9, soil_type="Clay")
"""

import os, json
import numpy as np
import pandas as pd
import joblib
from sklearn.preprocessing import PolynomialFeatures


MODEL_DIR    = os.path.dirname(os.path.abspath(__file__))
CLASS_THRESH = {"N": [35, 65], "P": [30, 55], "K": [28, 55]}


def _classify(value: float, nutrient: str) -> str:
    lo, hi = CLASS_THRESH[nutrient]
    if value < lo:   return "Low"
    if value < hi:   return "Medium"
    return "High"


class NPKPredictor:
    def __init__(self, model_dir: str = MODEL_DIR):
        self.model_dir = model_dir
        self._load()

    def _load(self):
        for nut in ("N", "P", "K"):
            p = os.path.join(self.model_dir, f"model_{nut}.pkl")
            if not os.path.exists(p):
                raise FileNotFoundError(f"Missing {p} — run auto_train.py first")

        self.models = {n: joblib.load(os.path.join(self.model_dir, f"model_{n}.pkl"))
                       for n in ("N","P","K")}
        self.le     = joblib.load(os.path.join(self.model_dir, "label_encoder.pkl"))

        with open(os.path.join(self.model_dir, "metadata.json")) as f:
            self.meta = json.load(f)

        self.feature_cols  = self.meta["feature_cols"]
        self.cfg           = self.meta.get("config_used", {})

    # ── Build the same feature vector that auto_train built ──────────
    def _make_features(self, moisture, ph, ec, temperature, soil_type):
        soil_enc = float(self.le.transform([soil_type])[0])

        row = {
            "Moisture"        : moisture,
            "pH"              : ph,
            "EC"              : ec,
            "Soil_Temperature": temperature,
            "Soil_Enc"        : soil_enc,
        }

        if self.cfg.get("ADD_LOG_FEATURES", True):
            row["log_EC"]        = np.log1p(ec)
            row["log_Moisture"]  = np.log1p(moisture)
            row["log_Temp"]      = np.log1p(temperature)

        if self.cfg.get("ADD_RATIO_FEATURES", True):
            row["EC_Moist_ratio"]   = ec   / (moisture    + 1e-6)
            row["Temp_EC_ratio"]    = temperature / (ec   + 1e-6)
            row["Moist_Temp_ratio"] = moisture / (temperature + 1e-6)

        if self.cfg.get("USE_POLY_FEATURES", True):
            base = np.array([[moisture, ph, ec, temperature]])
            degree = self.cfg.get("POLY_DEGREE", 2)
            poly = PolynomialFeatures(degree=degree, include_bias=False)
            pdata = poly.fit_transform(base)
            pnames_raw = poly.get_feature_names_out(["Moisture", "pH", "EC", "Soil_Temperature"])
            for name, val in zip(pnames_raw, pdata[0]):
                key = f"poly_{name.replace(' ','_')}"
                if key not in ("poly_Moisture", "poly_pH", "poly_EC", "poly_Soil_Temperature"):
                    row[key] = val

        # align to exact training column order
        X = np.array([[row.get(c, 0.0) for c in self.feature_cols]])
        return X

    def predict(self, moisture, ph, ec, temperature, soil_type="Loamy"):
        X = self._make_features(moisture, ph, ec, temperature, soil_type)
        preds = {n: float(round(self.models[n].predict(X)[0], 2)) for n in ("N","P","K")}
        return {
            "inputs" : {"moisture": moisture, "ph": ph, "ec": ec,
                        "temperature": temperature, "soil_type": soil_type},
            "N": preds["N"],  "N_class": _classify(preds["N"], "N"),
            "P": preds["P"],  "P_class": _classify(preds["P"], "P"),
            "K": preds["K"],  "K_class": _classify(preds["K"], "K"),
        }

    def predict_batch(self, readings: list) -> list:
        return [self.predict(r["moisture"], r.get("ph", 6.5), r["ec"], r["temperature"],
                             r.get("soil_type","Loamy")) for r in readings]

    def model_info(self):
        return self.meta

    def __repr__(self):
        r2 = self.meta.get("best_r2", {})
        return (f"<NPKPredictor  "
                f"N R²={r2.get('N','?')}  P R²={r2.get('P','?')}  K R²={r2.get('K','?')}>")


if __name__ == "__main__":
    m = NPKPredictor()
    print(m)
    r = m.predict(moisture=37.9, ph=6.65, ec=1.77, temperature=28.9, soil_type="Clay")
    print(f"N={r['N']} ({r['N_class']})  P={r['P']} ({r['P_class']})  K={r['K']} ({r['K_class']})")
