### IP-Symcon Modul // Withings
---
## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Systemanforderungen](#2-systemanforderungen)
3. [Installation](#3-installation)
4. [Konfiguration](#4-konfiguration)
5. [Visualisierung](#5-visualisierung)
6. [Datenhandling] (#6-datenhandling)
7. [Probleme](#7-probleme)
8. [Changelog](#8-changelog)
9. [ToDo Liste](#9-todo)

## 1. Funktionsumfang
Diese Modul holt die Gewichtsdaten und Blutdruckdaten vom Withingsserver
und speichert sie in Variablen. Daten werden standarmaessig nicht geloggt.
Dies kann aber aktiviert werden.
                                                                                                                  #
## 2. Systemanforderungen
- IP-Symcon ab Version 4.x
- Connect Modul mit funktionierender "ipmagic" Adresse
## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`https://github.com/1007/Symcon1007_Withings`

Instanz hinzufuegen.
Auswahlliste Sonstige ( Withings)

## 4. Konfiguration
Als erstes ueber den Button "Authentifizierung" die
OAuth2-Authentifizierung anstossen.
Eine Webseite ( Withings ) wird automatisch geoeffnet.
Wenn noch nicht angemeldet bitte anmelden.
Dann Withingsuser waehlen wenn mehrere Personen im Haushalt.
Den Zugriff der APP bestaetigen.
Bei Problemen gibt das Debugfenster eine Menge Infos.

#####Folgende Einstellungen sind individuell

Modul aktivieren

Gewichtswerte holen

Blutdruckwerte holen

Schlafsensorwerte holen

Abfrageintervall in Sekunden

Logging einschalten ( Logdatei in Ordner ../logs/Withings)

## 5. Visualisierung
Um die Daten im Webfront darzustellen einfach die Instance innerhalb einer
Kategorie verlinken. Sollen bestimmte Werte nicht angezeigt werden die
Variable haendisch auf unsichtbar setzen.

## 6. Datenhandling
Die Namen der erkannten Geraete zB
	Aura Sensor V2
	Withings Blood Pressure Monitor V2
	Withings WBS01
koennen nach dem Erstellen umbennant werden.
Identifizierung ueber die Ident.
Das Loggen der Daten kann fuer jede Variable einzeln im Tree
bestimmt werden.
## 7. Probleme
Beim Loeschen des Moduls werden die Variablen mitgeloescht die geloggten
Daten bleiben erhalten, werden im Archiv-Handler aber als
"Objekt #xxxxx existiert nicht" angezeigt.

In der API ueber OAuth2 gibt es im Moment noch keinen Zugriff
auf Name,Groesse,Geschlecht,Geburtstag. Deshalb bei neuer Instanz werden diese
Variablen leer bleiben. Unbedingt darauf achten die Groesse einzutragen.
Sonst bleibt der BMI leer.

## 8. Changelog
Version 2.1:
  - Erster Release
Version 2.2:
  - Werte werden nicht mehr automatisch geloggt
  - Anzeige im Webfront waehlbar
Version 2.3:
  - Waage mit Pulsmessung
Version 3.0  
  - Umstellung auf OAuth2
 
## 9. ToDo Liste
Da jetzt mit OAuth2 Zugriff erfolgt ist jetzt auch Zugriff auf
  Bewegungsdaten
	Geraete ueber die DeviceID verwalten.
	Pulswerte fuer Waage und Blutdruck noch nicht getrennt.

  