### IP-Symcon Modul // Withings
---
## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Systemanforderungen](#2-systemanforderungen)
3. [Installation](#3-installation)
4. [Konfiguration](#4-konfiguration)
5. [Visualisierung](#5-visualisierung)
6. [Datenhandling] (#6-data)
7. [Probleme](#7-problems)
8. [Changelog](#8-changelog)

## 1. Funktionsumfang
Diese Modul holt die Gewichtsdaten und Blutdruckdaten vom Withingsserver
und speichert sie in Variablen. Daten werden standarmaessig nicht geloggt.
Dies kann aber aktiviert werden.
                                                                                                                  #
## 2. Systemanforderungen
- IP-Symcon ab Version 4.x

## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`https://github.com/1007/Symcon1007_Withings`

Instanz hinzufuegen.
Auswahlliste Sonstige ( Withings)

## 4. Konfiguration
#####Folgende Einstellungen sind noetig

Zugangsdaten fuer Withings:
- Benutzername
- Userpasswort
- Kurzname des Benutzers
    
#####Folgende Einstellungen sind individuell

Modul aktivieren

Gewichtswerte holen

Gewichtswerte loggen

Gewichtswerte im Webfront anzeigen

Blutdruckwerte holen

Blutdruckwerte loggen

Blutdruckwerte im Webfront anzeigen

Abfrageintervall in Sekunden

Logging einschalten ( Logdatei in Ordner ../logs/Withings)

## 5. Visualisierung
Um die Daten im Webfront darzustellen einfach die Instance innerhalb einer
Kategorie verlinken. 

## 6. Datenhandling
Um Daten von einer anderen Variable zu uebernehmen ist zuerst das 
"Loggen der Werte" zu deaktivieren. Bereits geloeschte Daten im ArchivHandler
loeschen. Dann Werte von alter Variablen in die neue mit 
"Datensaetze ueberfuehren" uebernehmen. Logging wieder einschalten.
Variable Neu-Aggregieren nicht vergessen ( Versionsabhaengig ).
Ein Abwaehlen des Loggen loescht keine Daten. Dies muss wenn gewollt
mit Hand gemacht werden.

## 7. Probleme
Beim Loeschen des Moduls werden die Variablen mitgeloescht die geloggten
Daten bleiben erhalten, werden im Archiv-Handler aber als
"Objekt #xxxxx existiert nicht" angezeigt.

## 8. Changelog
Version 2.1:
  - Erster Release
Version 2.2:
  - Werte werden nicht mehr automatisch geloggt
  - Anzeige im Webfront waehlbar