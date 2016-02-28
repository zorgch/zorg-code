# Inhaltsverzeichnis
[TOC]

# Zorg Code in lokale Instanz inkl. Datenbank aufsetzen

## Voraussetzungen

* Lokal muss ein Apache-Webserver mit **PHP5+** sowie **MySQL** vorhanden sein:
Am einfachsten geht das mittels [MAMP (Mac)](http://www.mamp.info/en/downloads/), [WAMP (Windows)](http://www.wampserver.com/en/) und [LAMP (Linux)](http://www.turnkeylinux.org/lampstack)
* Um die MySQL-Datenbank lokal zu Konfigurieren & Verwalten am besten ein MySQL-Manager verwenden.
z.B. für Mac OS X mit [Sequel Pro](http://www.sequelpro.com/download).


* * *


## Git und Mercurial einrichten

1. Git installieren: [OS X](http://git-scm.com/downloads) | [Windows](http://code.google.com/p/msysgit/downloads/list)
2. Globalen Git Username & E-Mail konfigurieren mittels Terminal/Git Bash (Git benutzt diese globale E-Mail Adresse für alle späteren Commits/Code-Einreichungen):

        git config --global user.name "DEIN USERNAME"

        git config --global user.email "DEINE E-MAIL"

3. Mercurial installieren: [OS X](http://mercurial.selenic.com/) | [Windows](http://tortoisehg.bitbucket.org/download/)

Detailliertere Anleitungen zur Installation von Git & Mercurial gibt es auch hier:
[Bitbucket 101 - Mac OS X][1] | [Bitbucket 101 - Windows][2] | [Bitbucket 101 - Linux][3]


## Bitbucket einrichten

1. Falls noch nicht vorhanden, einen [Bitbucket Account eröffnen](https://bitbucket.org/account/signup/)
*Es empfiehlt sich, die gleiche E-Mailadresse zu verwenden wie zuvor via `git config` definiert!*
2. Account aktivieren er den Aktivierungs-Link in der E-Mail von Bitbucket
3. **Dein Bitbucket-Account muss jetzt dem [Zorg-Repository][4] hinzugefügt werden, bevor Du den Code auschecken kannst!**
4. Nimm Kontakt auf mit [[z]bert][5], [[z]keep3r][6] oder [IneX][7] für den Zugang zum Zorg-Repository.

> Dabei ist wichtig zu wissen, ob Du nur Read-only oder auch Schreib-Rechte benötigst (letzteres nur, wenn du aktiv am Code mitarbeiten möchtest)


## Clone des Zorg Repository erstellen (für lokales Zorg, ohne Entwicklung)

1. Das Terminal (OS X) resp. Git Bash (Windows) starten
2. Folgenden Befehl ausführen um das Zorg Repository auf Deinen Computer zu clonen:

        git clone https://bitbucket.org/zorgvorstand/zorg.ch.git /pfad/zum/lokalen/apache/webroot/

3. Im Apache Web-Root (auch "*htdocs/*" oder "*www/*") befindet sich jetzt eine Kopie des Zorg www-Verzeichnis mit sämtlichen Dateien von Zorg.

*Bevor Zorg aber lokal angezeigt werden kann muss zuerst noch [die Datenbank](#z-db-setup) eingerichtet werden!*


## Pull-Request des Zorg Repository erstellen (für lokale Entwicklung)

1. Erstelle im Zorg Repository einen neuen [Pull Request](https://bitbucket.org/zorgvorstand/zorg.ch/pull-request/new)
2. Alternativ geht das auch via Terminal/Git Bash mit folgendem Befehl:

        git pull https://bitbucket.org/zorgvorstand/zorg.ch.git

*Bevor Zorg aber lokal geöffnet werden kann muss zuerst noch [die Datenbank](#z-db-setup) eingerichtet werden!*

Eine ausführliche Anleitung zum Pull-Request und Forken ist hier zu finden: [OS X][8] | [Windows][8]


## Apache Virtual Host definieren

Es hat sich gezeigt, dass eine lokale Zorg Installation nicht sauber funktioniert mit User Sessions (nach dem Login), solange kein Virtueller Host im Apache Webserver definiert wurde. Deshalb machen wir das auch noch:

1. Im lokalen Apache Verzeichnis die Apache-Konfigurationsdatei "*httpd.conf*" in einem Editor öffnen
2. Sicherstellen, dass die Zeile "NameVirtualHost *:80" aktiviert ist (ggf. "#" am Anfang der Zeile entfernen!)
3. Folgenden VirtualHost in der "*httpd.conf*" Datei für den Zorg www-Ordner festlegen:
        
        ServerAdmin admin@mail.com
        DocumentRoot "http://www.zorg.ch/pfad/zum/lokalen/apache/webroot/zorg.ch/www/"
        ServerName localhost
        ServerAlias zorg.local *.zorg.local
    
4. Nun musst Du lokal noch den Hostnamen "*zorg.local*" in die hosts-Datei schreiben:
Unter OS X findet sich die Datei hier:

        /private/etc/hosts

5. Die Datei mit folgender neuen Zeile ergänzen:

        127.0.0.1    zorg.local


## Lokale Zorg Datenbank einrichten<a name="z-db-setup"></a>

1. Kontaktiere [[z]bert][5], [[z]keep3r][6] oder [IneX][7] für einen aktuellen Zorg DB-Dump
2. Auf der lokalen MySQL-Installation eine neue Datenbank "*zooomclan*" anlegen
3. Nun muss für diese Datenbank ein **User "zooomclan"** mit dem gleichen Passwort wie definiert in der Datei ["mysql_login.inc.php"][9] erstellt werden (benötigt sämtliche Rechte, minimum aber Select, Insert, Update & Delete)
4. Jetzt kann der DB-Dump der Zorg Datenbank in die lokale "*zooomclan*"-Datenbank importiert werden


## Deine lokale Zorg Kopie sollte nun über den Webbrowser erreichbar sein!
### Teste das mit [http://zorg.local/](http://zorg.local)


* * *


# Code Anpassungen oder Erweiterungen?

## 1. BEVOR Änderungen eingecheckt werden, **immer zuerst ein "*git pull*"** vom aktuellsten Code Stand machen!

## 2. Dokumentation nicht vergessen!
Damit auch anderen nachvollziehen können, was für Anpassungen am Zorg Code Du vorgenommen hast und was die Intention dahinter ist, dokumentiere alles bitte entsprechend! (Fast) jeder Kommentar ist besser, als keiner... Um es Dir auch möglichst einfach zu machen, findest Du folgend entsprechende Schnipsel, die Du für die Dokumentation im Code adaptieren kannst.

Da wir dem [phpDoc Standard][10] folgen, können wir daraus nämlich auch laufend eine schöne Webseite der gesamten Dokumentation automatisch generieren lassen!

### Klassen dokumentieren
    /**
     * Titel meiner Klasse
     * 
     * Lange Beschreibung meiner tollen
     * Klasse. Hier kann ich auf mehreren
     * Zeilen schreiben, was ich will.
     * Natürlich müssen nicht alle Parameter
     * für die Doku verwendet werden.
     * 
     * @author Wer hats gemacht?
     * @date 23.23.2023
     * @version 1.0
     * @package Zorg
     * @subpackage Kategorie (z.B. "Addle", "Events", o.ä.)
     */
    class MeineKlasse
    {
        ...
    }

### Funktionen dokumentieren
    /**
     * Titel meiner Funktion
     * 
     * Lange Beschreibung meiner tollen
     * Funktion. Hier kann ich auf mehreren
     * Zeilen schreiben, was ich will.
     * 
     * @author Wer hats gemacht?
     * @version 1.0 (Version meiner Funktion)
     * @since 1.0 (aktuelle Version der übergeordneten Klasse)
     *
     * @param integer $user_id Eine User-ID muss der Funktion übergeben werden
     * @global array Datenbank-Informationen in {$db}
     * @global array User-Informationen in {$user}
     * @return string/boolean/integer/array
     */
    function MeineFunktion($user_id)
    {
        global $db, $user; // Für DB Operationen & User Variablen
        
        // Code...
    }

Achtung: wenn eine Funktion AUSSERHALB einer Klasse geschrieben wird, bitte noch folgende 2 Zeilen in der Beschreibung ergänzen (bei Funktionen innerhalb von Klassen sollte dieser Kontext bereits gegeben sein):

     * @package Zorg
     * @subpackage Kategorie (z.B. "Addle", "Events", o.ä.)

### Variablen in Klassen dokumentieren
    /**
     * Beschreibung meiner Variable mit Angabe des Typs
     *
     * @var array
     */
    ...

### Konstanten dokumentieren
    /**
     * Beschreibung meiner Konstante
     */
    ...

### Alles andere dokumentieren

#### Unfertige Stellen / offene Arbeiten
To-Dos in Codeblöcken können einfach im PHPDoc Block ergänzt werden mit folgender Zeile:

    * @ToDo Hier habe ich noch etwas zu erledigen, und zwar...

#### Inline Kommentare
    // Kommentar einer einfachen Abfrage, Variable, usw.

#### Includes / Requires
    /**
     * File Includes
     */
    ...

## Und so sieht unsere Zorg Code Doku damit dann aus: [Zorg Code phpDocumentor Doku](11)


* * *

# Zorg Code Pull auf xoli
Irgendwie muss ja der Zorg Code vom Bitbucket Repository auch auf xoli, den www-Server, gelangen :) Grundsätzlich funktioniert das gleich, wie wenn man es lokal auf seinem Entwicklungsrechner macht, nur halt dass wir auf dem Server mittels Console arbeiten müssen.

## Git serverseitig konfigurieren
**Vorab: sämtliche der folgend beschriebenen Aktionen kann nur mittels System User ```su``` erfolgen!**

### Verzeichnisse
* Grundsätzlich ist das Git Repo auf dem Server unter folgenden Pfad gecloned worden:

        /var/www

* Um Git Einstellungen oder eben Pull Requests zu machen, arbeitet man daher direkt im /var/ Verzeichnis
(*nicht* in /var/www !)
* An dieser Stelle sei noch erwähnt, dass das ```/var/data```-Verzeichnis unverzichtbar ist für zorg.ch, aber nicht der Git-Codeversionierung auf Bitbucket unterliegt!

### Git Konfigurationen
* Bestehende Repo Verknüpfung(en) auflisten

        $ git remote -v

* Repo Verknüpfung aktualisieren (z.B. neue URL, User/PW hat geändert, usw.)

        $ git remote set-url origin https://zorgvorstand:API_TOKEN@bitbucket.org/zorgvorstand/zorg.ch.git

* Der API-Token für den Tem-User "ZorgVorstand" kann nur ein Administrator dieses Bitbucket-Teams [auslesen bzw. neu generieren](https://bitbucket.org/account/user/zorgvorstand/api-key/) wenn notwendig

## Code vom Bitbucket-Repo auf xoli pullen
* Mit Deinem persönlichen User mittels SSH auf den Server (xoli) verbinden

        $ ssh username@zorg.ch

* Von dort nun den ```su``` User starten

        $ su
        $ [Passwort]

* Nach erfolgreichem login ins /var/ Verzeichnis wechseln

        $ cd /var/

### **Initiales Setup**: Repo EINMALIG auf xoli runterladen
#### Git installieren

        $ apt-get update
        $ apt-get install git

#### Zorg Code Repo herunterladen:

        $ git init .
        $ git remote add origin https://zorgvorstand:API_TOKEN@bitbucket.org/zorgvorstand/zorg.ch.git
        $ git pull origin master

#### Berechtigungen auf die Verzeichnisse richtig setzen (der apache2-Prozess läuft standardmässig als root)

```/www/```-Verzeichnis

        $ chmod 755 $(find /var/www/ -type d)
        $ chmod 644 $(find /var/www/ -type f)

```/data/files/```-Verzeichnis und Files

        $ chmod 777 $(find /var/data/files/ -type d)
        $ chmod 644 $(find /var/data/files/ -type f)

```/data/upload/```-Verzeichnis

        $ chmod 755 /var/data/upload/

```/smartylib/```-Verzeichnisse (Smarty braucht 777!)

        $ chmod 777 /var/data/smartylib/templates_c
        $ chmod 777 /var/data/smartylib/cache

Jetzt noch apache2 konfigurieren mit den notwendigen Konfigurationsdateien:

* /etc/apache2/sites-available/**000-default.conf**: https://bitbucket.org/snippets/zorgvorstand/AdMq8
* Testen der Konfiguration mittels:

        $ apachectl configtest


### **Regelmässig**: den Codestand AKTUALISIEREN
Wenn Änderungen ins Zorg Code Repository auf Bitbucket committed & pushed wurden, müssen diese serverseitig natürlich noch heruntergeladen werden damit diese auch auf [www.zorg.ch](http://www.zorg.ch) vorhanden sind. Das geht wie folgt:

* Git Pull-Request im /var/ auslösen

        $ git pull

* **DONE** - die Änderungen müssten jetzt auch auf [www.zorg.ch](http://www.zorg.ch) aktiv sein.


[1]: https://confluence.atlassian.com/pages/viewpage.action?pageId=269981802 "Set up Git and Mercurial (Mac OS X)"
[2]: https://confluence.atlassian.com/display/BITBUCKET/Set+up+Git+and+Mercurial "Set up Git and Mercurial (Windows)"
[3]: https://confluence.atlassian.com/pages/viewpage.action?pageId=269982882 "Set up Git and Mercurial (Linux)"
[4]: https://bitbucket.org/zorgvorstand/zorg.ch "zorg.ch - Bitbucket"
[5]: https://bitbucket.org/rnatau/ "Bert"
[6]: https://bitbucket.org/nicoraschle/ "Nico"
[7]: https://bitbucket.org/oraduner/ "Oliver"
[8]: https://confluence.atlassian.com/pages/viewpage.action?pageId=271942986 "Fork a Repo, Compare Code, and Create a Pull Request (Mac OSX/Linux)"
[9]: https://bitbucket.org/zorgvorstand/zorg.ch/src/3dd86099c6445a606c4fa81882f06b6567633baf/www/includes/mysql_login.inc.php?at=master "mysql_login.inc.php"
[10]: http://en.wikipedia.org/wiki/PHPDoc "PHPDoc auf Wikipedia"
[11]: http://www.zorg.ch/zorgcode/ "Zorg Code phpDocumentor Doku"
[12]: https://bitbucket.org/account/user/zorgvorstand/api-key/ "ZorgVorstand Bitbucket API Key verwalten"