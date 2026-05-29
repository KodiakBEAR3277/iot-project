#include <Arduino.h>
#include "config.h"
#include "wifi_manager.h"
#include "sensors.h"
#include "actuators.h"
#include "api_client.h"

unsigned long lastSensorPost   = 0;
unsigned long lastActuatorPoll = 0;

void setup() {
    Serial.begin(115200);
    delay(1000);

    Serial.println("=== Heat Safety Monitor ===");

    initActuators();
    initSensors();
    connectWifi();

    updateOLED("Heat Safety\nMonitor\nConnected!");
    delay(2000);
}

void loop() {
    ensureWifi();
    unsigned long now = millis();

    // ── POST sensor data ──────────────────────────────────────
    if (now - lastSensorPost >= INTERVAL_SENSOR_POST) {
        lastSensorPost = now;

        // DHT11
        DHTReading dht = readDHT();
        if (dht.valid) {
            postSensor("dht11_temp",     dht.temperature, "C");
            postSensor("dht11_humidity", dht.humidity,    "%");
            Serial.printf("[DHT] Temp: %.1f C  Humidity: %.1f%%\n",
                          dht.temperature, dht.humidity);
        }

        // PIR
        bool motion = readPIR();
        postPIR(motion);
        Serial.printf("[PIR] Motion: %s\n", motion ? "YES" : "NO");
    }

    // ── POLL actuators ────────────────────────────────────────
    if (now - lastActuatorPoll >= INTERVAL_ACTUATOR_POLL) {
        lastActuatorPoll = now;

        ActuatorCommand cmd = pollActuators();
        if (cmd.valid) {
            setBuzzer(cmd.buzzer);
            setLED(cmd.led);
            updateOLED(cmd.oledMessage);
            Serial.printf("[ACT] Buzzer: %s | LED: %s |OLED: %s\n",
                          cmd.buzzer ? "ON" : "OFF",
                          cmd.led    ? "ON" : "OFF",
                          cmd.oledMessage.c_str());
            Serial.printf("[ACT] Raw buzzer value: %d\n", cmd.buzzer);
        }
    }
}