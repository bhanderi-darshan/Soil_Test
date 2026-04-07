import os
import pandas as pd
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.model_selection import train_test_split, RandomizedSearchCV
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report


def tune_random_forest(X_train, y_train, target_name):
    param_dist = {
        "n_estimators": [100, 200, 300, 400],
        "max_depth": [None, 10, 15, 20, 25],
        "min_samples_split": [2, 5, 10],
        "min_samples_leaf": [1, 2, 3, 4],
        "class_weight": [None, "balanced", "balanced_subsample"],
    }

    base = RandomForestClassifier(random_state=42, n_jobs=-1)
    search = RandomizedSearchCV(
        base,
        param_dist,
        n_iter=20,
        cv=3,
        scoring="accuracy",
        n_jobs=-1,
        random_state=42,
        verbose=2,
    )
    search.fit(X_train, y_train)

    print(f"{target_name} best params: {search.best_params_}")
    return search.best_estimator_


def main():
    print("Starting train_and_predict script")
    print(f"Working directory: {os.getcwd()}")

    data_file = "soil_dataset_ready.csv"
    if not os.path.exists(data_file):
        raise FileNotFoundError(f"Data file not found: {data_file}")

    df = pd.read_csv(data_file)
    print(f"Loaded data with {len(df)} rows")

    required_cols = [
        "ec",
        "temperature",
        "moisture",
        "previous_crop",
        "soil_type",
        "nitrogen_status",
        "phosphorus_status",
        "potassium_status",
    ]
    missing = [c for c in required_cols if c not in df.columns]
    if missing:
        raise ValueError(f"Missing required columns: {missing}")

    le_crop = LabelEncoder()
    le_soil = LabelEncoder()
    df["previous_crop"] = le_crop.fit_transform(df["previous_crop"])
    df["soil_type"] = le_soil.fit_transform(df["soil_type"])

    df["ec_temp"] = df["ec"] * df["temperature"]
    df["ec_moisture"] = df["ec"] * df["moisture"]

    X = df[
        [
            "ec",
            "temperature",
            "moisture",
            "previous_crop",
            "soil_type",
            "ec_temp",
            "ec_moisture",
        ]
    ]

    yN = leN.fit_transform(df["nitrogen_status"])
    yP = leP.fit_transform(df["phosphorus_status"])
    yK = leK.fit_transform(df["potassium_status"])

    X_train, X_test, yN_train, yN_test, yP_train, yP_test, yK_train, yK_test = train_test_split(
        X,
        yN,
        yP,
        yK,
        test_size=0.2,
        random_state=42,
        stratify=yN,
    )

    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)
    X_full_scaled = scaler.transform(X)

    def tune_and_eval(y, target_label):
        X_tr, X_te, y_tr, y_te = train_test_split(
            X,
            y,
            test_size=0.2,
            random_state=42,
            stratify=y,
        )

        scaler_t = StandardScaler()
        X_tr_s = scaler_t.fit_transform(X_tr)
        X_te_s = scaler_t.transform(X_te)

        model = tune_random_forest(X_tr_s, y_tr, target_label)
        y_pred_test = model.predict(X_te_s)

        print(f"{target_label} accuracy on test set: {accuracy_score(y_te, y_pred_test):.4f}")
        print(f"{target_label} classification report:\n{classification_report(y_te, y_pred_test)}")

        full_pred = model.predict(X_full_scaled)
        return model, full_pred

    modelN, predN_full = tune_and_eval(yN, "Nitrogen")
    modelP, predP_full = tune_and_eval(yP, "Phosphorus")
    modelK, predK_full = tune_and_eval(yK, "Potassium")

    predN_full = leN.inverse_transform(predN_full)
    predP_full = leP.inverse_transform(predP_full)
    predK_full = leK.inverse_transform(predK_full)

    df["predicted_nitrogen"] = predN_full
    df["predicted_phosphorus"] = predP_full
    df["predicted_potassium"] = predK_full

    output_file = "prediction_output.csv"
    df.to_csv(output_file, index=False)
    print(f"Prediction CSV saved successfully -> {output_file}")
    print(df[["ec", "temperature", "moisture", "predicted_nitrogen", "predicted_phosphorus", "predicted_potassium"]].head())


if __name__ == "__main__":
    leN = LabelEncoder()
    leP = LabelEncoder()
    leK = LabelEncoder()
    main()