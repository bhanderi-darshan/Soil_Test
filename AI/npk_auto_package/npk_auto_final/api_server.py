"""
NPK Live Prediction API
========================
python api_server.py

POST /predict          single sensor reading
POST /predict/batch    multiple readings
GET  /health           server status + R² scores
GET  /model/info       full metadata
"""

import os, traceback
from datetime import datetime
from flask      import Flask, request, jsonify
from predictor  import NPKPredictor

app   = Flask(__name__)
MODEL = NPKPredictor()


def _ts():
    return datetime.utcnow().isoformat() + "Z"

def _ok(d, code=200):
    d["timestamp"] = _ts()
    return jsonify(d), code

def _err(msg, code=400):
    return jsonify({"error": msg, "timestamp": _ts()}), code


@app.get("/health")
def health():
    info = MODEL.model_info()
    return _ok({
        "status"  : "ok",
        "best_r2" : info.get("best_r2"),
        "achieved": info.get("achieved"),
        "features": len(info.get("feature_cols", [])),
    })

@app.post("/predict")
def predict():
    """
    Body: {"moisture": 37.9, "ec": 1.77, "temperature": 28.9, "soil_type": "Clay"}
    """
    if not request.is_json:
        return _err("Content-Type must be application/json")
    b = request.get_json()
    try:
        result = MODEL.predict(
            moisture    = float(b["moisture"]),
            ec          = float(b["ec"]),
            temperature = float(b["temperature"]),
            soil_type   = str(b.get("soil_type", "Loamy")),
        )
    except (KeyError, ValueError) as e:
        return _err(str(e), 422)
    except Exception:
        return _err(traceback.format_exc(), 500)
    return _ok(result)

@app.post("/predict/batch")
def predict_batch():
    if not request.is_json:
        return _err("Content-Type must be application/json")
    b = request.get_json()
    readings = b.get("readings", [])
    if not isinstance(readings, list) or not readings:
        return _err("'readings' must be a non-empty list")
    try:
        results = MODEL.predict_batch(readings)
    except Exception as e:
        return _err(str(e), 422)
    return _ok({"count": len(results), "predictions": results})

@app.get("/model/info")
def model_info():
    return _ok(MODEL.model_info())


if __name__ == "__main__":
    port = int(os.environ.get("PORT", 5000))
    print(f"\n  NPK API  →  http://0.0.0.0:{port}")
    print(f"  {MODEL}\n")
    app.run(host="0.0.0.0", port=port, debug=False)
