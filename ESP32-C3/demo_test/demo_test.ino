#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiManager.h>

WiFiManager wm;

// Device ID (must match database devices table)
String deviceID = "SOIL001";

// Server path
const char* serverName = "http://10.18.134.1/Product/receive.php";

// Example sensor values (temporary until sensors connected)
float soilMoisture = 45.2;
float ecValue = 1.3;
float temperature = 29.5;

void setup() {

  Serial.begin(115200);
  delay(2000);

  Serial.println("Booting device...");

  // Start WiFi setup portal if not connected
  if (!wm.autoConnect("SoilSensor_Setup")) {
    wm.startConfigPortal("SoilSensor_Setup");
  }

  Serial.println("WiFi Connected!");
  Serial.println(WiFi.localIP());

  sendData();   // send once after boot
}

void sendData() {

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected");
    return;
  }

  HTTPClient http;

  String serverPath = String(serverName) +
                      "?device=" + deviceID +
                      "&moisture=" + soilMoisture +
                      "&ec=" + ecValue +
                      "&temp=" + temperature;

  Serial.println("Sending request:");
  Serial.println(serverPath);

  http.begin(serverPath);

  int httpResponseCode = http.GET();

  if (httpResponseCode > 0) {

    Serial.print("Response Code: ");
    Serial.println(httpResponseCode);

    String payload = http.getString();
    Serial.println(payload);

  } else {

    Serial.print("HTTP Error: ");
    Serial.println(httpResponseCode);

  }

  http.end();
}

void loop() {

  delay(10000);

  sendData();   // send every 10 seconds (testing mode)

}