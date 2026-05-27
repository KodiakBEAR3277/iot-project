#pragma once
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include "config.h"

// POST a sensor reading
bool postSensor(const char* sensorType, float value, const char* unit) {
    HTTPClient http;
    http.begin(API_BASE "/sensors");
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");

    JsonDocument doc;
    doc["sensor_type"] = sensorType;
    doc["value"]       = value;
    doc["unit"]        = unit;

    String body;
    serializeJson(doc, body);

    int code = http.POST(body);
    bool ok  = (code == 200 || code == 201);
    if (!ok) Serial.printf("[API] POST %s failed: %d\n", sensorType, code);
    http.end();
    return ok;
}

// POST a PIR (boolean) reading
bool postPIR(bool triggered) {
    HTTPClient http;
    http.begin(API_BASE "/sensors");
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Accept", "application/json");

    JsonDocument doc;
    doc["sensor_type"] = "pir";
    doc["triggered"]   = triggered;

    String body;
    serializeJson(doc, body);

    int code = http.POST(body);
    bool ok  = (code == 200 || code == 201);
    if (!ok) Serial.printf("[API] POST pir failed: %d\n", code);
    http.end();
    return ok;
}

struct ActuatorCommand {
    bool buzzer;
    String oledMessage;
    bool valid;
};

// GET /api/actuators
ActuatorCommand pollActuators() {
    ActuatorCommand cmd = { false, "", false };
    HTTPClient http;
    http.begin(API_BASE "/actuators");
    http.addHeader("Accept", "application/json");

    int code = http.GET();
    if (code == 200) {
        JsonDocument doc;
        DeserializationError err = deserializeJson(doc, http.getStream());
        if (!err) {
            cmd.buzzer      = doc["buzzer"]["state"].as<bool>();
            cmd.oledMessage = doc["oled"]["message"].as<String>();
            cmd.valid       = true;
        } else {
            Serial.println("[API] JSON parse error");
        }
    } else {
        Serial.printf("[API] GET actuators failed: %d\n", code);
    }
    http.end();
    return cmd;
}