### IP-Symcon Modul // BatterieMonitor
---

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang) 
2. [Systemanforderungen](#2-systemanforderungen)
3. [Installation](#3-installation)
4. [Befehlsreferenz](#4-befehlsreferenz)
5. [Changelog](#5-changelog) 

## 1. Funktionsumfang
Dieses Modul liest alle Batterie-Variablen von Aktoren aus, gibt die Informationen in einem Array zurück, stellt alle
Batterie-Aktoren in einer Tabelle dar und erzeugt eine 2. Tabelle mit allen Aktoren die eine leere Batterie haben.

**Aktuell unterstützte Hersteller:**
- FHT
- FS20 HMS
- HomeMatic
- Z-Wave

**Benachrichtigung**
Je nachdem, ob eine Benachrichtigung per EMail/Push/Skript gewünscht ist, muss noch die entsprechende Instanz ausgewählt und auf aktiv
gesetzt werden (Haken setzen). Eine Benachrichtigung erfolgt IMMER wenn eine leere Batterie erkannt wird! Wechselt ihr eine Batterie nicht,
dann werdet ihr im Update-Intervall jeweils erneut benachrichtigt, bis die Batterien gewechselt wurden. Außerdem könnt ihr eine Boolean-Variable
definieren und damit die Benachrichtigungen steuern (Bool-Variable TRUE = Benachrichtigung, FALSE = keine Benachrichtigung).
- Pro Aktor mit leerer Batterie wird eine Benachrichtigung gesendet! Bei 3 leeren Aktoren sind das 3 Benachrichtigungen!

Ihr könnt auch ein eigenes Skript festlegen, welches zur Benachrichtigung verwendet wird. Dieses Skript wird bei Erkennung eines Aktor mit
leerer Batterie ausgeführt. Hier kann man dann Benachrichtigungen über Sonos, Enigma2-Nachricht, SMS, ... einrichten.
Für eigene Aktionen stehen einem im ausgewählten Skript die folgenden Variablen zur Verfügung:
```
$_IPS["BMON_Name"] (Name des Aktor)
$_IPS["BMON_ParentName1"] (Name des Parent-Objekt1 vom Aktor)
$_IPS["BMON_ParentName2"] (Name des Parent-Objekt2 vom Aktor)
$_IPS["BMON_ParentName3"] (Name des Parent-Objekt3 vom Aktor)
$_IPS["BMON_Hersteller"] (Hersteller des Aktor)
$_IPS["BMON_ID"] (ID/Serial des Aktor)
$_IPS["BMON_Batterie"] (Batteriezustand OK/LEER)
$_IPS["BMON_Text"] (Der Benachrichtigungstext inkl. "Übersetzung" der Variablen)
$_IPS["BMON_LetztesUpdateTS"] (Datum und Uhrzeit, wann die Batterie-Variable zuletzt aktualisiert wurde)
$_IPS["BMON_LetztesUpdateSEK"] (Sek. seit letzter Aktualisierung der Batterie-Variable)
```

#### Beispiel-Skript für eigene Aktion
```php
<?
$Enigma2BYinstanzID = 12345; // InstanzID des Enigma2-Modul eintragen
IPS_LogMessage("BatterieMonitor", $_IPS["BMON_Text"]); // Schreibt den Text ins IPS-Log (zu sehen im Meldungen-Fenster in der IPS-Console)
Enigma2BY_SendMsg($Enigma2BYinstanzID, $_IPS["BMON_Text"], 3, 10); // Zeigt 10 Sekunden lang eine Alarm-Nachricht über einen Enigma2-Receiver an
?>
```

**In der Modul-Instanz könnt ihr folgende Einstellungen vornehmen:**
- Hintergrundfarbe (HEX Farbcode)
- Textfarbe (HEX Farbcode)
- Textfarbe OK (HEX Farbcode)
- Textfarbe LEER (HEX Farbcode)
- Textgröße
- Textausrichtung (links,zentriert,rechts)
- Namen von bis zu 3 übergeordneten Objekten (Parents) + Anzeige in HTML-Tabelle
- Aktualisierungsintervall (std. 21600 Sek = 6 Std)
- Benachrichtigungseinstellungen (Push-Nachricht, EMail, Skript)
- Benachrichtigungsversand über Variable steuern (true=Benachrichtigung,false=keine Benachrichtigung) - z.B. IPS-Location-Variable "Ist es Tag"


## 2. Systemanforderungen
- IP-Symcon ab Version 4.x

## 3. Installation
Über die Kern-Instanz "Module Control" folgende URL hinzufügen:

`git://github.com/BayaroX/BY_BatterieMonitor.git`


## 4. Befehlsreferenz
```php
  BMON_Update($InstanzID);
```
Liest alle Batterie-Variablen aus und schreibt die Informationen zu den Batterie-Aktoren in 2 Variablen (HTMLBox).

```php
  BMON_Alle_Auslesen($InstanzID);
```
Liest alle Batterie-Aktoren aus, gibt die Informationen in einem Array zurück und schreibt die Informationen in
eine String-Variable (HTMLBox). Wenn keine Aktoren mit Batterie vorhanden sind, wird "false" zurückgegeben.

```php
  BMON_Leere_Auslesen($InstanzID);
```
Liest alle Batterie-Aktoren mit leeren Batterien aus, gibt die Informationen in einem Array zurück und schreibt
die Informationen in eine String-Variable (HTMLBox). Wenn keine Aktoren mit leeren Batterie vorhanden sind,
wird "false" zurückgegeben.


## 5. Changelog
Version 1.0:
  - Erster Release
  
Version 1.1:
  - NEU # Textausrichtung in den HTML-Tabellen kann eingestellt werden (links,zentriert,rechts)
  - NEU # Benachrichtigung, wenn Aktoren mit leeren Batterien erkannt wurden (Push-Nachricht, EMail, Skript)
  - NEU # Weitere Daten vom Aktor (Hersteller, ID, Letztes Variablen-Update Timestamp, Zeit in Sekunden seit letztem Variablen-Update)
  - FIX # Doppelte Aktoren-Einträge werden aus Array/HTML-Tabelle entfernt (Der 1. gefundene Eintrag wird behalten)
  
Version 1.2
  - NEU # Sortierung von Array und HTML-Tabellen einstellbar (nach Name, Parent-Name [wenn aktiv], Hersteller, ID, ...)
  - NEU # Test-Benachrichtigung (mit fiktiven Daten, aber eigenem Text) kann aus der Instanz gesendet werden

Version 1.3
  - NEU # Benachrichtigungsversand über Variable steuern (wenn Variable TRUE, dann Benachrichtigungen senden, wenn FALSE, dann nicht)
  - NEU # Bis zu 3 Namen von Parent-Objekten können ausgelesen werden (z.B. Etage, Raum und Gebäude) und sind dann in Array und
          den HTML-Tabellen verfügbar. Maximal kann man 9 Ebenen nach oben gehen, zum Auslesen der Namen.
