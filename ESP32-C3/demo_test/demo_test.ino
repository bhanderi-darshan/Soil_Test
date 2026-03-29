#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "Bhanderi";
const char* password = "12112005";
const char* serverName = "http://10.18.134.1/Product/receive.php";

float soilMoisture = 45.2;
float ecValue = 1.3;
float temperature = 29.5;

void setup() {
  Serial.begin(115200);
  delay(2000); // ← IMPORTANT: Give ESP32-C3 USB time to reconnect

  Serial.println("\n\nBooting...");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) { // ← timeout added
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi Connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nWiFi FAILED! Check SSID/Password or signal.");
  }
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("Sending data to server...");

    HTTPClient http;

    String serverPath = String(serverName) +
                        "?moisture=" + soilMoisture +
                        "&ec=" + ecValue +
                        "&temp=" + temperature;

    Serial.println("URL: " + serverPath);

    http.begin(serverPath.c_str());
    http.setTimeout(5000); // ← 5 second timeout so it doesn't hang

    int httpResponseCode = http.GET();

    if (httpResponseCode > 0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      String payload = http.getString();
      Serial.println("Response: " + payload);
    } else {
      Serial.print("HTTP Error: ");
      Serial.println(http.errorToString(httpResponseCode));
    }

    http.end();

  } else {
    Serial.println("WiFi disconnected! Reconnecting...");
    WiFi.reconnect();
    delay(3000);
  }

  delay(5000);
}