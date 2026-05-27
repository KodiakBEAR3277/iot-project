#pragma once
#include <Adafruit_SSD1306.h>
#include <Adafruit_GFX.h>
#include <Wire.h>
#include "config.h"

Adafruit_SSD1306 oled(OLED_WIDTH, OLED_HEIGHT, &Wire, -1);

void initActuators() {
    // Buzzer
    pinMode(PIN_BUZZER, OUTPUT);
    digitalWrite(PIN_BUZZER, HIGH);

    // OLED
    Wire.begin(OLED_SDA, OLED_SCL);
    if (!oled.begin(SSD1306_SWITCHCAPVCC, OLED_ADDRESS)) {
        Serial.println("[OLED] Init failed");
    } else {
        oled.clearDisplay();
        oled.setTextSize(1);
        oled.setTextColor(SSD1306_WHITE);
        oled.setCursor(0, 0);
        oled.println("Booting...");
        oled.display();
    }
}

void setBuzzer(bool on) {
    if (on) {
        tone(PIN_BUZZER, 2000);
    } else {
        noTone(PIN_BUZZER);
    }
}

void updateOLED(const String& message) {
    oled.clearDisplay();
    oled.fillRect(0, 0, OLED_WIDTH, OLED_HEIGHT, SSD1306_BLACK); // force clear
    oled.setTextSize(1);
    oled.setTextColor(SSD1306_WHITE);

    String msg = message;
    int start = 0;
    int line  = 0;
    while (start < (int)msg.length() && line < 4) {
        int sep = msg.indexOf('|', start);
        String chunk = (sep == -1) ? msg.substring(start) : msg.substring(start, sep);
        chunk.trim();
        oled.setCursor(0, line * 12);
        oled.println(chunk);
        if (sep == -1) break;
        start = sep + 1;
        line++;
    }
    oled.display();
}