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

// Replace with your registered station_id and passkey
const int station_id = 0; // Use your assigned station ID number
const char* passkey = "station_passkey";  // Replace with your actual passkey

// Replace with your network credentials
const char* ssid = "local_sside";
const char* password = "ssid_password";

// Server URL
const char* serverName = "https://www.cijeweatherhub.site/wp-content/plugins/weather-hub/post-weather-data.php";

// Sensor Setup
#define USE_DHT 
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

// Variable for sleep interval (in seconds)
const uint64_t sleep_interval_seconds = 6 * 3600; // 6 hours in seconds
const uint64_t sleep_interval_us = sleep_interval_seconds * 1000000ULL;

const int wifi_timeout_ms = 20000; // 20 seconds timeout for WiFi connection

//#define LED_BUILTIN 2  // Built-in LED pin (change if necessary)

void setup() {
  Serial.begin(115200);

  // Initialize sensors
  #ifdef USE_DHT
    dht.begin();
    Serial.println("DHT22 sensor initialized...");
  #else
    if (!bme.begin(BME280_I2C_ADDR)) {
      Serial.println("BME280 sensor initialization failed!");
    } else {
      Serial.println("BME280 sensor initialized...");
    }
  #endif

  // Connect to WiFi with timeout
  if (!connectToWiFi()) {
    Serial.println("Failed to connect to WiFi. Going back to sleep...");
    goToSleep();
  }

  // Post weather data once for testing
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
      pressure = 0;
    #else
      temperature = bme.readTemperature();
      humidity = bme.readHumidity();
      pressure = bme.readPressure() / 100.0F;
    #endif

    wind_speed = analogRead(A0) * (5.0 / 1023.0); 

    if (isnan(temperature) || isnan(humidity)) {
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
                             "&wind_speed=" + String(wind_speed);

    Serial.print("HTTP Request Data: ");
    Serial.println(httpRequestData);

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.print("Response Code: ");
      Serial.println(httpResponseCode);
      Serial.print("Response: ");
      Serial.println(response);

      if (response.indexOf("Invalid station_id or passkey") != -1) {
        Serial.println("Error: station_id or passkey did not match!");
      } else if (response.indexOf("Station not found") != -1) {
        Serial.println("Error: Station not found! Please register your station.");
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

// Function to put ESP32 to sleep
void goToSleep() {
  // Turn off the built-in LED before going to sleep
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, LOW);  // Turn off the LED

  Serial.println("Going to sleep...");
  esp_sleep_enable_timer_wakeup(sleep_interval_us);
  esp_deep_sleep_start();
}