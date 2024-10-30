// ******************************************
// Weather Hub Database Connection Test - CIJE Weather Hub
// 
// - You must register your weather station to receive a unique station ID. 
// - Register your station at: https://www.cijeweatherhub.site/register-weather-station
// - You will need a registered station_id # and passkey to successfully post data to the database.
// ******************************************

#include <WiFi.h>
#include <HTTPClient.h>
#include <DHT.h>
#include <Adafruit_BME280.h>
#include "esp_sleep.h"

// Replace with your network credentials
const char* ssid = "local_ssid";
const char* password = "ssid_password";

// Server URL
const char* serverName = "https://www.cijeweatherhub.site/wp-content/plugins/cije-weatherhub-wp-plugin/core/includes/classes/post-weather-data.php"; // Correct URL

// Replace with your registered station_id and passkey
const int station_id = 0; // Use your assigned station ID number
const char* passkey = "station_passkey";  // Replace with your actual passkey


// Sensor Setup
#define USE_DHT // Uncomment this if using DHT sensor
#ifdef USE_DHT
  #define DHTPIN 17
  #define DHTTYPE DHT22
  DHT dht(DHTPIN, DHTTYPE);
#else
  #define BME280_I2C_ADDR 0x76
  Adafruit_BME280 bme;
#endif

// Placeholder for wind speed (from anemometer)
float wind_speed = 0.0;

// Placeholder for rain sensor value
int rain_sensor_pin = 34; // Assuming the rain sensor is connected to analog pin 34
float rain_inches = 0.0;

// Variable for sleep interval (in seconds)
const uint64_t sleep_interval_seconds = 6 * 3600; // 6 hours in seconds
const uint64_t sleep_interval_us = sleep_interval_seconds * 1000000ULL;

// WiFi connection timeout (in milliseconds)
const unsigned long wifi_timeout_ms = 30000; // 30 seconds

void setup() {
  Serial.begin(115200);

  // Connect to Wi-Fi
  if (!connectToWiFi()) {
    Serial.println("Failed to connect to WiFi. Going to sleep.");
    goToSleep();
  }

  // Initialize sensors
  #ifdef USE_DHT
    dht.begin();
  #else
    if (!bme.begin(BME280_I2C_ADDR)) {
      Serial.println("Could not find a valid BME280 sensor, check wiring!");
      while (1);
    }
  #endif

  // Post weather data
  postWeatherData();

  // Go to deep sleep after posting data
  goToSleep();
}

void loop() {
  // Nothing to do in the loop; the ESP will sleep after posting data.
}

// Function to connect to WiFi with a timeout
bool connectToWiFi() {
  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi...");

  unsigned long startAttemptTime = millis();
  while (WiFi.status() != WL_CONNECTED) {
    if (millis() - startAttemptTime >= wifi_timeout_ms) {
      Serial.println("WiFi connection timeout.");
      return false; // Failed to connect within the timeout
    }
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nConnected to WiFi");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
  return true;
}

// Function to post weather data
void postWeatherData() {
  if (WiFi.status() == WL_CONNECTED) {
    float temperature, humidity, pressure;

    #ifdef USE_DHT
      temperature = dht.readTemperature();
      humidity = dht.readHumidity();
      pressure = 0.0; // DHT does not provide pressure
    #else
      temperature = bme.readTemperature();
      humidity = bme.readHumidity();
      pressure = bme.readPressure() / 100.0F; // Convert Pa to hPa
    #endif

    // Read wind speed (dummy value for testing)
    wind_speed = analogRead(A0) * (5.0 / 1023.0); 

    // Read rain sensor value
    int rain_sensor_value = analogRead(rain_sensor_pin);
    rain_inches = (rain_sensor_value / 1024.0) * 1.57; // Convert analog value to inches

    if (isnan(temperature) || isnan(humidity) || isnan(pressure) || isnan(wind_speed) || isnan(rain_inches)) {
      Serial.println("Failed to read sensor data!");
      return;
    }

    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "station_id=" + String(station_id) +
                             "&passkey=" + String(passkey) +
                             "&temperature=" + String(temperature) +
                             "&humidity=" + String(humidity) +
                             "&pressure=" + String(pressure) +
                             "&wind_speed=" + String(wind_speed) +
                             "&rain_inches=" + String(rain_inches);

    Serial.print("HTTP Request Data: ");
    Serial.println(httpRequestData);

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.print("Response Code: ");
      Serial.println(httpResponseCode);
      Serial.print("Response: ");
      Serial.println(response);

      if (response.indexOf("Invalid station ID or passkey") != -1) {
        Serial.println("Error: Invalid station ID or passkey");
      } else if (response.indexOf("Data out of range") != -1) {
        Serial.println("Error: Data out of range");
      } else if (response.indexOf("Post too soon") != -1) {
        Serial.println("Error: Post too soon. Please wait an hour.");
      } else if (response.indexOf("Failed to insert data") != -1) {
        Serial.println("Error: Failed to insert data");
      } else {
        Serial.println("Data posted successfully");
      }
    } else {
      Serial.print("Error on sending POST: ");
      Serial.println(httpResponseCode);
    }

    http.end();
  } else {
    Serial.println("WiFi Disconnected");
  }
}

// Function to go to deep sleep
void goToSleep() {
  Serial.println("Going to sleep now...");
  esp_sleep_enable_timer_wakeup(sleep_interval_us);
  esp_deep_sleep_start();
}