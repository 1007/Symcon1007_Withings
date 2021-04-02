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
8. [Benachrichtigungen](#8-callbacks)
9. [Probleme](#9-probleme)
10. [Changelog](#10-changelog)
11. [ToDo Liste](#11-todo)

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

Abfrageintervall in Sekunden ( empfohlen 3600 )

Logging einschalten ( Logdatei in Ordner ../logs/Withings)

Alarmscript ( wird aufgerufen wenn bei einem Update die Authorisierung NOK )

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

## 8. Benachrichtigungen
Sobald Daten auf dem Withingsserver ankommen , kann man sich benachrichtigen lassen.
Die passiert ueber eine Webhookadresse.
Der Withingsserver ruft diese auf wenn neue Daten vorhanden. (sehr zeitnah).
Besonders interessant fuer den Sleepsensor (zB aufstehen).
Dazu muss man auf der Konfiguration ein Script auswahlen welches aufgerufen wird vom Modul
und eine CallbackURL welche dem Withingsserver bei jedem Update mitgeteilt wird.
Wichtig ! Wenn man die CallbackURL leert laesst wird versucht die IPMagic-Adresse zu senden.
Diese wird von Withings nicht akzeptiert. Bisher konnte mir Withings das nicht erklaeren.
Diese URL muss verschiedene Konditionen erfuellen (SSl, Antwort unter 2 Sekunden etc.)
Einfach die URL wie man den IPS-Server erreicht. Das Modul ergaenzt die Hook-Daten.
Im Debugfenster sollte es so ausehen wenn akzeptiert:
`DoCurl | https://wbsapi.withings.net/notify?action=subscribe&access_token=...........&callbackurl=https://xxx.xxx.xxx/hook/Withingsxxxx&appli=50&comment=SubscribeSleep`
`ProcessHookData[2089]`
`DoCurl | {"status":0,"body":{}}`
Wenn das so ist wird euer angegebenes Script in weniger als einer Minute bei Neuigkeiten aufgerufen.
Ich habe bei allen Moeglichkeiten nur ein Script angegeben und entscheide im Script was zu tun ist.
Beispiel:

`<?php`

    // $appli = Typ 1       = Gewichtsdaten
    // $appli = Typ 2       = Temperaturdaten
    // $appli = Typ 4       = Blutdruckdaten
    // $appli = Typ 16      = Aktivitaetsdaten
    // $appli = Typ 44      = Schlafdaten
    // $appli = Typ 46      = User Aktionen 
    // $appli = Typ 50      = Bett belegt
    // $appli = Typ 51      = Bett frei
    
    $userid 	= @$_IPS['userid'];
    $startdate 	= @date("d.m.Y H:i:s",$_IPS['startdate']);
    $enddate 	= @date("d.m.Y H:i:s",$_IPS['enddate']);
    $appli 		= @$_IPS['appli'];
    
    $appli = intval($appli);
    IPS_Logmessage(basename(__FILE__),"User:".$userid." ".$startdate." - " .$enddate." Typ:".$appli);
..









## 9. Probleme

Beim Loeschen des Moduls werden die Variablen mitgeloescht die geloggten
Daten bleiben erhalten, werden im Archiv-Handler aber als
"Objekt #xxxxx existiert nicht" angezeigt.
Bei der Synchronisierung von den Smartphone Aktivitaeten haengt es
machmal.. Abhilfe APP (Health Mate ) neu starten.

In der API ueber OAuth2 gibt es im Moment noch keinen Zugriff
auf Name,Groesse,Geschlecht,Geburtstag.
Deshalb bei neuer Instanz werden diese Variablen leer bleiben.
Unbedingt darauf achten die Groesse einzutragen sonst bleibt der BMI leer.

## 10. Changelog
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

## 11. ToDo Liste
	Pulswerte fuer Waage und Blutdruck noch nicht getrennt.( Nur haendisch )

  



