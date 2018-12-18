- [zorg Code auf lokaler Instanz einrichten](#zorg-code-auf-lokaler-instanz-einrichten)
  * [Voraussetzungen](#voraussetzungen)
  * [Lokalen Clone des zorg Codes aufsetzen](#lokalen-clone-des-zorg-codes-aufsetzen)
  * [Apache Virtual Host definieren](#apache-virtual-host-definieren)
  * [Lokale zorg Datenbank einrichten](#lokale-zorg-datenbank-einrichten)
- [Code Anpassungen vornehmen und an zorg mitwirken](#code-anpassungen-vornehmen-und-an-zorg-mitwirken)
  * [Dokumentation nicht vergessen!](#dokumentation-nicht-vergessen-)
  * [Pull-Request mit deinen Änderungen erstellen](#pull-request-mit-deinen--nderungen-erstellen)
- [zorg Code & Website auf xoli aufsetzen](#zorg-code---website-auf-xoli-aufsetzen)
  * [Git serverseitig konfigurieren](#git-serverseitig-konfigurieren)
  * [Neusten Codestand regelmässig aus dem Git Repo auf xoli pullen](#neusten-codestand---regelm-ssig---aus-dem-git-repo-auf-xoli-pullen)
  * [Initiales Setup: Repo EINMALIG auf xoli runterladen](#--initiales-setup----repo-einmalig-auf-xoli-runterladen)

# zorg Code auf lokaler Instanz einrichten

## Voraussetzungen
Lokal muss ein Apache-Webserver mit **PHP 5.6.x** sowie **MySQL 5.6** vorhanden sein:
Am einfachsten geht das mittels [MAMP (macOS)][1], [WAMP (Windows)][2] und [LAMP (Linux)][3]. Zukünftig geplant ist ein Docker Container, aber der [z]keep3r macht nicht fürschi damit...

### PHP
Aktuell ist der zorg Code bis und mit **PHP 5.6.37** lauffähig.
Support für **PHP 7.x** ist "*work in progress*" – [siehe hier][13] und [hier][14].

### MySQL
Die zorg-DB ist primär mit **MySQL 5.6** bzw. älter kompatibel, mit Deaktivierung einiger Settings aber auch mit **MySQL 5.7.x** lauffähig.

Folgende 5.7.x Settings werden aufgrund einiger Row Settings und old-school Queries (noch) nicht unterstützt und [sollten deaktiviert werden][15]:
`ONLY_FULL_GROUP_BY`, `NO_ZERO_IN_DATE` & `NO_ZERO_DATE`

Um die MySQL-Datenbank lokal zu konfigurieren & verwalten am besten ein MySQL-Manager verwenden z.B. für macOS mit [Sequel Pro](http://www.sequelpro.com/download).

### GitHub-Account erstellen
Ist ++nicht++ zwingend notwendig weil das zorg Code Repository bzw. der Code öffentlich verfügbar ist und daher von jedem heruntergeladen und gecloned werden kann.

> Falls Du aktiv am Code mitarbeiten bzw. deine Changes comitten möchtest, benötigst Du einen GitHub-Account und musst zudem in der [Kenner-Gruppe auf GitHub][4] hinzugefügt werden. Um diese Berechtigungen zu kriegen nimm bitte Kontakt auf mit [[z]bert][5], [[z]keep3r][6], [IneX][7] oder einem anderen Kenner.

### Git einrichten (optional)

1. Git installieren [wie hier beschrieben](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
2. Globalen Git Username & E-Mail konfigurieren mittels Terminal/Git Bash (Git benutzt diese globale E-Mail Adresse für alle späteren Commits - aka Code-Einreichungen):

        $ git config --global user.name "DEIN USERNAME"
        $ git config --global user.email "DEINE E-MAIL"

> Um in das zorg Code Repository comitten zu können muss Du mindestens die gleiche E-Mailadresse, wie verlinkt in deinem GitHub-Account, verwenden!*

### Docker Container
WIP @raschle

## Lokalen Clone des zorg Codes aufsetzen
Am einfachsten klickst Du auf der GitHub Repository-Seite einfach oben rechts auf den grünen "Clone or download"-button – oder Du ziehst Dir den neusten Release von [hier][16] und verschiebst alle Files lokal in das gewünschte Webroot vom Apache.

Wenn du dafür zu fest Geek bist, dann...
1. Das Terminal-Shell (macOS/Linux) resp. Git Bash (Windows) starten
2. Folgenden Befehl ausführen, um das zorg Code Repository direkt von GitHub auf Deinen Computer herunterzuladen:

        $ git clone https://github.com/zorgch/zorg-code.git /pfad/zum/lokalen/apache/webroot/

3. Im Apache Web-Root (auch "*htdocs/*" oder "*www/*") befindet sich jetzt eine Kopie des zorg www-Verzeichnis mit sämtlichen Dateien von zorg.

> Bevor zorg aber lokal angezeigt werden kann, musst Du zuerst noch einen Hosts-Eintrag und die Datenbank einrichten!*

## Apache Virtual Host definieren
Es hat sich gezeigt, dass eine lokale zorg Installation nicht sauber funktioniert mit User Sessions (nach dem Login), solange kein virtueller Host im Apache Webserver definiert wurde. Deshalb machen wir das auch noch.

Du kannst dir entweder die [zorg Apache Configs aus dem entsprechenden GitHub Repo][17] ziehen und anwenden - oder Du gehst den Quick'n'dirty way:
1. Im lokalen Apache-Verzeichnis die Konfigurationsdatei "*httpd.conf*" in einem Editor öffnen
2. Sicherstellen, dass die Zeile "NameVirtualHost *:80" aktiviert ist (ggf. "#" am Anfang der Zeile entfernen!)
3. Folgenden VirtualHost in der "*httpd.conf*" Datei für den zorg www-Ordner festlegen:
        
        ServerAdmin admin@mail.com
        DocumentRoot "/pfad/zum/lokalen/apache/webroot/zorg-code/www/"
        ServerName localhost
        ServerAlias zorg.local *.zorg.local

### Hosts-Eintrag
Nun musst Du lokal noch den Hostnamen "*zorg.local*" in die hosts-Datei schreiben:
Unter macOS findet sich die Datei hier:

        /private/etc/hosts

Die Datei mit folgender neuen Zeile ergänzen:

        127.0.0.1    zorg.local


## Lokale zorg Datenbank einrichten
> Kontaktiere [[z]bert][5], [[z]keep3r][6] oder [IneX][7] für einen aktuellen zorg DB-Dump

> Details siehe [MySQL database export, import and setup](migration/initial-setup/mysql-database.md)
1. Auf dem lokalen MySQL-Server eine neue Datenbank - z.B. `zorg` - anlegen und einen User mit sämtlichen Rechten (minimum aber Select, Insert, Update & Delete-Berechtigungen) dafür konfigurieren
2. Den zorg DB-Dump nun dort importieren

Damit zorg lokal mit dieser DB und dem gewählten User läuft, musst Du eine im [zorg www][9] eine MySQL-PHPdatei anlegen und konfigurieren:
1. unter `/zorg-code/www/includes/` erstelle eine Datei mit Bezeichnung `mysql_login.inc.local.php`
2. kopiere den folgenden Codes in die Datei und passe die Parameter entsprechend deiner Umgebung an:

```
<?php
/** MySQL database login information */
define('MYSQL_HOST',	'127.0.0.1');
define('MYSQL_DBNAME',	'...');
define('MYSQL_DBUSER',	'...');
define('MYSQL_DBPASS',	'...');
```

### Deine lokale zorg Kopie sollte nun über den Webbrowser erreichbar sein! Teste das mit [http://zorg.local/](http://zorg.local)


# Code Anpassungen vornehmen und an zorg mitwirken
> BEVOR du Änderungen am Code vornimmst bzw. einchecken willst, immer zuerst den aktuellsten Code Stand mittels `git pull` ziehen!

## Dokumentation nicht vergessen!
Damit auch anderen nachvollziehen können, was für Anpassungen am zorg Code Du vorgenommen hast und was die Intention dahinter ist, dokumentiere alles bitte entsprechend! (Fast) jeder Kommentar ist besser, als keiner... Um es Dir auch möglichst einfach zu machen, findest Du folgend entsprechende Schnipsel, die Du für die Dokumentation im Code adaptieren kannst.

Da wir dem [phpDoc Standard][10] folgen, können wir daraus nämlich auch laufend eine schöne Webseite der gesamten Dokumentation automatisch generieren lassen!

> Und so sieht unsere zorg Code Doku damit dann aus: [zorg Code phpDocumentor Doku](11)

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
     * @package zorg
     * @subpackage Kategorie (z.B. "Addle", "Events", o.ä.)
     * @author Dein-Name
     * @version 1.0 (aktuelle Version der Klasse)
     * @since 1.0 dd-mm-yyyy Klasse hinzugefügt
     * @since x.x dd-mm-yyyy Klassenänderungen/Change-log der Klasse
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
     * @author Dein-Name
     * @version 1.0 (aktuelle Version der Funktion)
     * @since 1.0 dd-mm-yyyy Funktion hinzugefügt
     * @since x.x dd-mm-yyyy Funktionsänderungen/Change-log der Funktion
     *
     * @param integer $user_id Eine User-ID muss der Funktion übergeben werden
     * @param string|array $params Parameter als String oder Array für meine Funktion - default: null
     * @global array Datenbank-Informationen in {$db}
     * @global array User-Informationen in {$user}
     * @return string/boolean/integer/array
     */
    function MeineFunktion($user_id, $params=null)
    {
        global $db, $user; // Für DB Operationen & User Variablen
        
        // Code...
    }

> Hinweis: wenn eine Funktion AUSSERHALB einer Klasse geschrieben wird, bitte noch die Package-Informationen im phpDoc Block ergänzen (bei Funktionen innerhalb von Klassen ist dieser Kontext bereits gegeben):

     * @package zorg
     * @subpackage Kategorie (z.B. "Addle", "Events", o.ä.)

### Variablen in Klassen dokumentieren
    /**
     * Beschreibung meiner Variable (oder mehreren) mit Angabe des Typs
     * @var array $meinevar1 Beschreibung...
     * @var string $meinevar2 Beschreibung...
     */
    ...

### File includes dokumentieren
    /**
     * Kurzbeschreibung einer eingebundenen Datei (oder mehreren).
     * @include	datei.inc.php Funktion: _include_ | Required: nein
     * @include	datei2.inc.php Funktion: _require_once_ | Required: ja
     */
    include_once( __DIR__ . '/datei.inc.php');
    require_once( __DIR__ . '/datei2.inc.php');
    ...

### Konstanten dokumentieren
    /**
     * Beschreibung meiner Konstante/n
     * @const WEBROOT Contains the absolute path to the Webroot directory
     * @const SITEURL Contains the root URL of my website
     */
    if (!defined('WEBROOT')) define('WEBROOT', '/', true);
    if (!defined('SITEURL')) define('SITEURL', 'zorg.ch', true);
    ...

### Inline Kommentare
    /** Kurzbeschreibung einer einfachen Abfrage, Variable, usw. */

### Offene Arbeiten markieren
To-Dos in Codeblöcken können einfach im PHPDoc Block ergänzt werden mit folgendem Verweis:

    * @TODO Hier habe ich noch etwas zu erledigen, und zwar...

### Notwendige Fixes / mögliche Fehlerquellen markieren
To-Dos mit höherer Prio, also etwas das unbedingt gefixt oder optimiert werden sollte, können mit folgendem Verweis markiert werden:

    * @FIXME das hier muss refactored werden, weil es performance-intensiv ist

## Pull-Request mit deinen Änderungen erstellen

TBD

# zorg Code & Website auf xoli aufsetzen
Irgendwie muss ja der zorg Code vom Git Repository auch auf xoli, den www-Server, gelangen :) Grundsätzlich funktioniert das gleich, wie wenn man es lokal auf seinem Entwicklungsrechner macht, nur halt dass wir auf dem Server mittels Console arbeiten müssen.

## Git serverseitig konfigurieren
**Vorab: sämtliche der folgend beschriebenen Aktionen kann nur mittels System User `su` erfolgen!**

### Verzeichnisse
* Grundsätzlich ist das Git Repo auf dem Server unter folgenden Pfad gecloned worden:

        /var/www

* Um Git Einstellungen oder eben Pull Requests zu machen, arbeitet man daher direkt im /var/ Verzeichnis (*nicht* in /var/www !)
* An dieser Stelle sei noch erwähnt, dass das `/var/data`-Verzeichnis unverzichtbar ist für zorg.ch, aber nicht der Git-Codeversionierung unterliegt!

### Git Konfigurationen
TBD

## Neusten Codestand **regelmässig** aus dem Git Repo auf xoli pullen
* Mit Deinem persönlichen User mittels SSH auf den Server (xoli) verbinden

        $ ssh username@zorg.ch

* Nach erfolgreichem login ins Webroot-Verzeichnis von xoli wechseln

        $ cd /var/

* Von dort kann nun mittels `su`-Rechten ein Git Pull gestartet werden

        $ su git pull

* **DONE** - die Änderungen müssten jetzt auch auf [zorg.ch][8] aktiv sein.

## **Initiales Setup**: Repo EINMALIG auf xoli runterladen
### Git installieren

        $ apt-get update
        $ apt-get install git

### zorg Code Repo herunterladen:
TBD

### Berechtigungen auf die Verzeichnisse richtig setzen

`/www/`-Verzeichnis

        $ chmod 755 $(find /var/www/ -type d)
        $ chmod 644 $(find /var/www/ -type f)

`/data/files/`-Verzeichnis und Files

        $ chmod 777 $(find /var/data/files/ -type d)
        $ chmod 644 $(find /var/data/files/ -type f)

`/data/upload/`-Verzeichnis

        $ chmod 755 /var/data/upload/

`/smartylib/`-Verzeichnisse (Smarty braucht `777`)

        $ chmod 777 /var/data/smartylib/templates_c
        $ chmod 777 /var/data/smartylib/cache

### apache2 konfigurieren
Jetzt noch apache2 konfigurieren mit den notwendigen Konfigurationsdateien:

* siehe [zorgch/xoli-apache-configs][17]
* Testen der Konfiguration mittels:

        $ apachectl configtest

**That's it** - zorg.ch läuft nun auf dem xoli

[1]: https://www.mamp.info/ "MAMP for macOS"
[2]: https://www.wampserver.com/ "WAMP for Windows"
[3]: https://www.turnkeylinux.org/lampstack "LAMP for Linux"
[4]: https://github.com/orgs/zorgch/teams/kenner "zorg Verein - Kenner"
[5]: https://github.com/rnatau "Bert"
[6]: https://github.com/raschle "Nico"
[7]: https://github.com/oliveratgithub "Oli"
[8]: https://zorg.ch/ "zorg.ch"
[9]: https://github.com/zorgch/zorg-code/tree/master/www/includes "/zorg-code/www/includes"
[10]: https://phpdoc.org/ "phpDocumentor website"
[11]: https://zorg.ch/zorgcode/ "zorg Code phpDocumentor Doku"
[13]: https://zorg.ch/bug/733 "Bug #733 - PHP Version updaten"
[14]: https://github.com/orgs/zorgch/projects/2 "zorg Coding Roadmap - GitHub Project"
[15]: https://www.sitepoint.com/quick-tip-how-to-permanently-change-sql-mode-in-mysql/ "How to Permanently Change SQL Mode in MySQL 5.7"
[16]: https://github.com/zorgch/zorg-code/releases "zorg Code Releases"
[17]: https://github.com/zorgch/xoli-apache-configs "zorgch/xoli-apache-configs"