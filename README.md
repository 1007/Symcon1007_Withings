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
Dieses Modul holt Daten vom Withingsserver und speichert sie in Variablen.
Geraete werden automatisch erkannt und angelegt.
Daten werden standarmaessig nicht geloggt.
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

Benachrichtigungen (Beta)

Abfrageintervall in Sekunden ( empfohlen 3600 )

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
	Thermo
	Activity

koennen nach dem Erstellen umbenannt werden. Identifizierung ueber die Ident.
Aktivitaetsdaten koennen auch ueber verknuepfte Smartphonedaten kommen.
Die hier aufgelisteten Geraete sind bei mir im Einsatz und somit getestet.
Das Loggen der Daten kann/muss fuer jede Variable einzeln im Tree bestimmt werden.

## 7. Moegliche Daten

	Withings WBS01
		Updatezeit
		Batterie
		Gewicht 
		Fettfrei Anteil
		Fett Prozent
		BMI
	Body Cargo
		Updatezeit
		Batterie 
		Muskelmasse
		Wasseranteil
		Gewicht
		Fett Anteil
		Knochenmasse
		Fettfrei Anteil
		Puls 
		Pulswellengeschwindigkeit
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
		Updatezeit
		Batterie
		Puls
		Diastolic
		Systolic
	Thermo
		Updatzeit
		Batterie
		Temperatur
		Koerpertemperatur
		Hauttemperatur
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
	IntradayActivity ( nur ab IPSymcon Version 5.1 )
		Updatezeit
		Distanze
		Hoehenmeter
		Kalorien
		Schritte



Aktivitaetsdaten sind zB mit der WithingsAPP verknuepfte Daten aus dem Smartphone.
Activity sind taegliche Daten ( Summe )
IntradayActivity sind detailierte Daten ueber den ganzen Tag. ( zB minuetlich ),
aber erst ab IPSymcon Version 5.1 da die Daten am Ende vom Tag mit dem richtigen
Zeitstempel in die Datenbank eingetragen werden. 

## 8. Probleme

Beim Loeschen des Moduls werden die Variablen mitgeloescht die geloggten
Daten bleiben erhalten, werden im Archiv-Handler aber als
"Objekt #xxxxx existiert nicht" angezeigt.
Bei der Synchronisierung von den Smartphone Aktivitaeten haengt es
machmal.. Abhilfe APP (Health Mate ) neu starten.

In der API ueber OAuth2 gibt es im Moment noch keinen Zugriff
auf Name,Groesse,Geschlecht,Geburtstag.
Deshalb bei neuer Instanz werden diese Variablen leer bleiben.
Unbedingt darauf achten die Groesse einzutragen sonst bleibt der BMI leer.

Withings weist ipmagic Adressen als callbackurl fuer Benachrichtigungen
im Moment noch ab.

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

Version 4.0  
  - zusaetzliche Daten 

## 10. ToDo Liste
	Pulswerte fuer Waage und Blutdruck noch nicht getrennt.( Nur haendisch )

  
