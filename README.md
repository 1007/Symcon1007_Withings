### IP-Symcon Modul // Withings
---
## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Systemanforderungen](#2-systemanforderungen)
3. [Installation](#3-installation)
4. [Konfiguration](#4-konfiguration)
5. [Visualisierung](#5-visualisierung)
6. [Datenhandling](#6-datenhandling)
7. [Moegliche Daten](#7-datas)
8. [Probleme](#8-probleme)
9. [Changelog](#9-changelog)
10. [ToDo Liste](#10-todo)

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
Auswahlliste Sonstige ( Withings )

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

Aktivitaetswerte holen

Abfrageintervall in Sekunden

Logging einschalten ( Logdatei in Ordner ../logs/Withings)

## 5. Visualisierung
Um die Daten im Webfront darzustellen einfach die Instance innerhalb einer
Kategorie verlinken. Sollen bestimmte Werte nicht angezeigt werden die
Variable haendisch auf unsichtbar setzen.

## 6. Datenhandling
Die Namen der erkannten Geraete zB
	
	Withings WBS01
	Body Cardio
	Aura Sensor V2
	Withings Blood Pressure Monitor V2
	Activity
	
koennen nach dem Erstellen umbennant werden. Identifizierung ueber die Ident.
Aktivitaetsdaten koennen auch ueber verknuepte Smartphonedaten kommen.
Das Loggen der Daten kann/muss fuer jede Variable einzeln im Tree
bestimmt werden.

## 7. Moegliche Daten
	
	Withings WBS01
		DatumUhrzeit
		Batterie
		Gewicht 
		Fettfrei Anteil
		Fett Prozent
		BMI
	Body Cargo
		DatumUhrzeit
		Batterie 
		Muskelmasse
		Wasseranteil
		Gewicht
		Fett Anteil
		Knochenmasse
		Fettfrei Anteil
		Puls 
		Pulswellengeschwindigkeit ( zZ von Withings deaktiviert )
		Fett Prozent
		BMI
	Aura Sensor V2
		Updatezeit
		Batterie
		Startzeit
		Endezeit
		Schlafdauer
		Einschlafzeit
		Aufstehzeit
		Leichtschlafphasen
		Tiefschlafphasen
		Wachphasen
		Schlafunterbrechungen
	Withings Blood Pressure Monitor V2
		DatumUhrzeit
		Batterie
		Puls
		Diastolic
		Systolic
	Activity
		Updatezeit
		Schritte
		Distanze
		Hoehenmeter
		Aktivitaetskalorien
		Gesamtkalorien
		Geringe Aktivitaet
		Hohe Aktivitaet
		Mittlere Aktivitaet
		

## 8. Probleme
Beim Loeschen des Moduls werden die Variablen mitgeloescht die geloggten
Daten bleiben erhalten, werden im Archiv-Handler aber als
"Objekt #xxxxx existiert nicht" angezeigt.

In der API ueber OAuth2 gibt es im Moment noch keinen Zugriff
auf Name,Groesse,Geschlecht,Geburtstag. Deshalb bei neuer Instanz werden diese
Variablen leer bleiben. Unbedingt darauf achten die Groesse einzutragen.
Sonst bleibt der BMI leer.

## 9. Changelog
Version 2.1:
  - Erster Release

Version 2.2:
  - Werte werden nicht mehr automatisch geloggt
  - Anzeige im Webfront waehlbar

Version 2.3:
  - Waage mit Pulsmessung

Version 3.0  
  - Umstellung auf OAuth2

Version 3.1  
  - zusaetzliche Daten 
  
## 10. ToDo Liste
	Pulswerte fuer Waage und Blutdruck noch nicht getrennt.

  