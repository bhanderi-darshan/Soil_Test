"""
NPK Predictor — Live Inference
================================
Load trained models and predict N, P, K from sensor readings.

Quick start:
    from predictor import NPKPredictor
    model = NPKPredictor()
    result = model.predict(moisture=37.9, ec=1.77, temperature=28.9, soil_type="Clay")
    print(result)
"""

import os
import json
import numpy as np
import joblib


MODEL_DIR = os.path.dirname(os.path.abspath(__file__))

SOIL_TYPES = ["Clay", "Loamy", "Peaty", "Sandy", "Silty"]

CLASS_THRESH = {"N": [35, 65], "P": [30, 55], "K": [28, 55]}

INPUT_BOUNDS = {
    "moisture":    (0,   100),
    "ec":          (0,   10),
    "temperature": (0,   60),
}


def _classify(value: float, nutrient: str) -> str:
    lo, hi = CLASS_THRESH[nutrient]
    if value < lo:
        return "Low"
    if value < hi:
        return "Medium"
    return "High"


class NPKPredictor:
    """
    Load models once, call predict() for every new sensor reading.

    Parameters
    ----------
    model_dir : str
        Directory containing model_N.pkl, model_P.pkl, model_K.pkl,
        label_encoder.pkl, and metadata.json.
        Defaults to the folder where this file lives.
    """

    def __init__(self, model_dir: str = MODEL_DIR):
        self.model_dir = model_dir
        self._load()

    def _load(self):
        self.models = {}
        for nut in ("N", "P", "K"):
            path = os.path.join(self.model_dir, f"model_{nut}.pkl")
            if not os.path.exists(path):
                raise FileNotFoundError(
                    f"Model file not found: {path}\n"
                    "Run train_model.py first to generate model files."
                )
            self.models[nut] = joblib.load(path)

        le_path = os.path.join(self.model_dir, "label_encoder.pkl")
        self.le = joblib.load(le_path)

        meta_path = os.path.join(self.model_dir, "metadata.json")
        with open(meta_path) as f:
            self.metadata = json.load(f)

    # ── Validation ────────────────────────────────────────────────────────────
    def _validate(self, moisture, ec, temperature, soil_type):
        errors = []

        for name, val in [("moisture", moisture), ("ec", ec), ("temperature", temperature)]:
            lo, hi = INPUT_BOUNDS[name]
            if not (lo <= val <= hi):
                errors.append(f"{name}={val} is out of expected range [{lo}, {hi}]")

        valid_soils = list(self.le.classes_)
        if soil_type not in valid_soils:
            errors.append(
                f"soil_type='{soil_type}' is not recognised. "
                f"Valid values: {valid_soils}"
            )

        if errors:
            raise ValueError("Input validation failed:\n  " + "\n  ".join(errors))

    # ── Core predict ──────────────────────────────────────────────────────────
    def predict(
        self,
        moisture: float,
        ec: float,
        temperature: float,
        soil_type: str = "Loamy",
        validate: bool = True,
    ) -> dict:
        """
        Predict N, P, K values from sensor readings.

        Parameters
        ----------
        moisture    : float  — Soil moisture in %            (e.g. 37.9)
        ec          : float  — Electrical conductivity dS/m  (e.g. 1.77)
        temperature : float  — Soil temperature in °C        (e.g. 28.9)
        soil_type   : str    — One of Clay/Loamy/Sandy/Peaty/Silty
        validate    : bool   — Run input range checks (default True)

        Returns
        -------
        dict with keys:
            inputs   — echo of sensor values
            N        — predicted Nitrogen value (mg/kg)
            P        — predicted Phosphorus value (mg/kg)
            K        — predicted Potassium value (mg/kg)
            N_class  — Low / Medium / High
            P_class  — Low / Medium / High
            K_class  — Low / Medium / High
        """
        if validate:
            self._validate(moisture, ec, temperature, soil_type)

        soil_enc = self.le.transform([soil_type])[0]
        X = np.array([[moisture, ec, temperature, soil_enc]])

        predictions = {nut: float(round(self.models[nut].predict(X)[0], 2))
                       for nut in ("N", "P", "K")}

        return {
            "inputs": {
                "moisture": moisture,
                "ec": ec,
                "temperature": temperature,
                "soil_type": soil_type,
            },
            "N":       predictions["N"],
            "P":       predictions["P"],
            "K":       predictions["K"],
            "N_class": _classify(predictions["N"], "N"),
            "P_class": _classify(predictions["P"], "P"),
            "K_class": _classify(predictions["K"], "K"),
        }

    # ── Batch predict (list of sensor dicts) ──────────────────────────────────
    def predict_batch(self, readings: list) -> list:
        """
        Predict for multiple sensor readings at once.

        Parameters
        ----------
        readings : list of dicts, each with keys:
            moisture, ec, temperature, soil_type (optional, defaults to 'Loamy')

        Returns
        -------
        list of prediction dicts (same format as predict())

        Example
        -------
        results = model.predict_batch([
            {"moisture": 37.9, "ec": 1.77, "temperature": 28.9},
            {"moisture": 55.0, "ec": 2.10, "temperature": 31.0, "soil_type": "Sandy"},
        ])
        """
        return [
            self.predict(
                moisture=r["moisture"],
                ec=r["ec"],
                temperature=r["temperature"],
                soil_type=r.get("soil_type", "Loamy"),
            )
            for r in readings
        ]

    # ── Model info ────────────────────────────────────────────────────────────
    def model_info(self) -> dict:
        """Return model metadata and performance metrics."""
        return self.metadata

    def __repr__(self):
        m = self.metadata.get("metrics", {})
        parts = [f"{nut} R²={m[nut]['r2']}" for nut in ("N", "P", "K") if nut in m]
        return f"<NPKPredictor  {' | '.join(parts)}>"


# ── Quick test when run directly ──────────────────────────────────────────────
if __name__ == "__main__":
    model = NPKPredictor()
    print(model)

    test_reading = dict(moisture=37.9, ec=1.77, temperature=28.9, soil_type="Clay")
    result = model.predict(**test_reading)

    print("\nSample prediction:")
    print(f"  Inputs   : Moisture={result['inputs']['moisture']}%  "
          f"EC={result['inputs']['ec']} dS/m  "
          f"Temp={result['inputs']['temperature']}°C  "
          f"Soil={result['inputs']['soil_type']}")
    print(f"  N = {result['N']:6.2f} mg/kg  ({result['N_class']})")
    print(f"  P = {result['P']:6.2f} mg/kg  ({result['P_class']})")
    print(f"  K = {result['K']:6.2f} mg/kg  ({result['K_class']})")

    print("\nModel metrics:")
    for nut, m in model.model_info()["metrics"].items():
        print(f"  {nut}  R²={m['r2']}  MAE={m['mae']}  RMSE={m['rmse']}  CV_R²={m['cv_r2']}")
