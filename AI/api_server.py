"""
NPK Prediction API Server
===========================
REST API to serve live NPK predictions from sensor data.

Run:
    python api_server.py

Then send POST requests to http://localhost:5000/predict

Endpoints:
    POST /predict       — single sensor reading
    POST /predict/batch — multiple readings at once
    GET  /health        — server + model status
    GET  /model/info    — model metrics & metadata
"""

import os
import json
import traceback
from datetime import datetime

from flask import Flask, request, jsonify
from predictor import NPKPredictor


app = Flask(__name__)
MODEL = NPKPredictor()


# ── Helpers ───────────────────────────────────────────────────────────────────
def _ok(data: dict, status: int = 200):
    data["timestamp"] = datetime.utcnow().isoformat() + "Z"
    return jsonify(data), status


def _err(message: str, status: int = 400):
    return jsonify({"error": message, "timestamp": datetime.utcnow().isoformat() + "Z"}), status


def _parse_float(body: dict, key: str, required: bool = True):
    if key not in body:
        if required:
            raise KeyError(f"Missing required field: '{key}'")
        return None
    try:
        return float(body[key])
    except (TypeError, ValueError):
        raise ValueError(f"Field '{key}' must be a number, got: {body[key]!r}")


# ── Routes ────────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    """Health check — confirms server and model are ready."""
    info = MODEL.model_info()
    return _ok({
        "status": "ok",
        "model": "NPK Random Forest",
        "model_type": info.get("model_type"),
        "n_estimators": info.get("rf_params", {}).get("n_estimators"),
        "metrics": {nut: info["metrics"][nut]["r2"] for nut in ("N", "P", "K")},
    })


@app.post("/predict")
def predict():
    """
    Predict N, P, K for a single sensor reading.

    Request body (JSON):
        {
          "moisture":    37.9,       # required — soil moisture %
          "ec":          1.77,       # required — electrical conductivity dS/m
          "temperature": 28.9,       # required — soil temperature °C
          "soil_type":   "Clay"      # optional — default: "Loamy"
        }

    Response:
        {
          "N": 72.43,  "N_class": "High",
          "P": 38.21,  "P_class": "Medium",
          "K": 55.10,  "K_class": "Medium",
          "inputs": { ... },
          "timestamp": "2024-01-15T10:30:00Z"
        }
    """
    if not request.is_json:
        return _err("Request body must be JSON (Content-Type: application/json)")

    body = request.get_json()

    try:
        moisture    = _parse_float(body, "moisture")
        ec          = _parse_float(body, "ec")
        temperature = _parse_float(body, "temperature")
        soil_type   = str(body.get("soil_type", "Loamy"))
    except (KeyError, ValueError) as e:
        return _err(str(e))

    try:
        result = MODEL.predict(moisture, ec, temperature, soil_type)
    except ValueError as e:
        return _err(str(e), 422)
    except Exception:
        return _err("Prediction failed: " + traceback.format_exc(), 500)

    return _ok(result)


@app.post("/predict/batch")
def predict_batch():
    """
    Predict N, P, K for multiple sensor readings at once.

    Request body (JSON):
        {
          "readings": [
            {"moisture": 37.9, "ec": 1.77, "temperature": 28.9, "soil_type": "Clay"},
            {"moisture": 55.0, "ec": 2.10, "temperature": 31.0}
          ]
        }

    Response:
        {
          "count": 2,
          "predictions": [ {...}, {...} ],
          "timestamp": "..."
        }
    """
    if not request.is_json:
        return _err("Request body must be JSON")

    body = request.get_json()
    readings = body.get("readings")

    if not isinstance(readings, list) or len(readings) == 0:
        return _err("'readings' must be a non-empty list")

    try:
        results = MODEL.predict_batch(readings)
    except (KeyError, ValueError) as e:
        return _err(str(e), 422)
    except Exception:
        return _err("Batch prediction failed: " + traceback.format_exc(), 500)

    return _ok({"count": len(results), "predictions": results})


@app.get("/model/info")
def model_info():
    """Return full model metadata, metrics, and feature importances."""
    return _ok(MODEL.model_info())


# ── Run ───────────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    print(f"\n  NPK Prediction API running on http://0.0.0.0:{port}")
    print(f"  Model: {MODEL}\n")
    app.run(host="0.0.0.0", port=port, debug=False)
