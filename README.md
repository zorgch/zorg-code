#### Übersicht ####
- [zorg Code standalone lokal einrichten](#zorg-code-standalone-lokal-einrichten)
  - [Voraussetzungen](#voraussetzungen)
  - [Clone des zorg Codes](#clone-des-zorg-codes)
  - [Verzeichnisstruktur](#verzeichnisstruktur)
  - [Apache Virtual Host konfigurieren](#apache-virtual-host-konfigurieren)
  - [zorg Datenbank](#zorg-datenbank)
- [Am zorg Coding mitwirken](#am-zorg-coding-mitwirken)
  - [Applikationssettings lokal überschreiben](#applikationssettings-lokal-berschreiben)
  - [Code Dokumentation](#code-dokumentation)
- [Neusten Codestand deployen](#neusten-codestand-deployen)
  - [Pull-Request mit Code-Änderungen erstellen](#pull-request-mit-code-änderungen-erstellen)
  - [Deployments](#deployments)
- [Initiales Server-Setup](#initiales-setup-repo-einmalig-auf-den-server-runterladen)

# zorg Code standalone lokal einrichten
## Voraussetzungen
Lokal muss ein Apache-Webserver mit [**PHP**](#php) sowie [**MySQL**](#mysql) vorhanden sein.
Am einfachsten geht das mittels [MAMP (macOS)][1], [WAMP (Windows)][2] und [LAMP (Linux)][3].

Zukünftig geplant ist ein Docker Container, aber der [[z]keep3r][6] macht nicht fürschi damit...

### PHP
* [Current][14]: **PHP 7.4.x**
* für <= [zorg Code 4.0.1][16]: PHP 5.6.37

### MySQL
* Current: **MySQL 5.7.x**
* für <= [zorg Code 4.0.1][16]: MySQL 5.6

Um die MySQL-Datenbank lokal zu konfigurieren & verwalten am besten ein MySQL-Manager verwenden z.B. für macOS mit [Sequel Ace](https://github.com/Sequel-Ace/Sequel-Ace/releases).

### GitHub-Account erstellen
Ist ++nicht++ zwingend notwendig weil das zorg Code Repository bzw. der Code öffentlich verfügbar ist und daher von jedem heruntergeladen und gecloned werden kann.

> Falls Du aktiv am Code mitarbeiten bzw. deine Changes comitten möchtest, benötigst Du einen GitHub-Account und musst zudem in der [Kenner-Gruppe auf GitHub][4] hinzugefügt werden. Um diese Berechtigungen zu kriegen nimm bitte Kontakt auf mit [kassiopaia][5], [[z]keep3r][6], [IneX][7] oder einem anderen Kenner.

### Git einrichten (optional)
Vorab auf GitHub einen Personal Access Token generieren: GitHub Developer Settings > [Personal access tokens](https://github.com/settings/tokens)

Am einfachsten und mit eingebauten Git ist die Arbeit/Bezug des zorg Codes mit einem Git-Programm wie z.B. [Fork](https://git-fork.com) zu handhaben (für Windows & macOS).

1. Optional kann Git lokal installiert werden [wie hier beschrieben](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
2. Danach lokal den globalen Git-User* konfigurieren mittels Terminal/Git Bash (Git benutzt diese globale E-Mail Adresse für alle späteren Commits - aka Code-Einreichungen):

  $ git config --global user.name "DEIN USERNAME"
  $ git config --global user.email "DEINE E-MAIL"
  $ git config --global user.password GITHUB-DEVELOPER-TOKEN

\* *Um in das zorg Code Repository comitten zu können muss Du mindestens die gleiche E-Mailadresse, wie verlinkt in deinem GitHub-Account, verwenden!*

### Docker Container
WIP [[z]keep3r][6],

## Clone des zorg Codes
Am einfachsten klickst Du auf der GitHub Repository-Seite einfach oben rechts auf den grünen "Clone or download"-button – oder Du ziehst Dir den neusten Release von [hier][16] und verschiebst alle Files lokal in das gewünschte Webroot vom Apache.

Wenn du dafür zu fest Geek bist, dann...
1. Das Terminal-Shell (macOS/Linux) resp. Git Bash (Windows) starten
2. Folgenden Befehl ausführen, um das zorg Code Repository direkt von GitHub auf Deinen Computer herunterzuladen:

  $ git clone https://github.com/zorgch/zorg-code.git /pfad/zum/lokalen/apache/webroot/

3. Im Apache Web-Root (auch "*htdocs/*" oder "*www/*") befindet sich jetzt eine Kopie des zorg www-Verzeichnis mit sämtlichen Dateien von zorg.

> Bevor zorg aber lokal angezeigt werden kann, musst Du zuerst noch einen Hosts-Eintrag und die Datenbank einrichten!*

## Verzeichnisstruktur
Folgende Verzeichnisse brauchst du nach dem lokalen Clone des Git-Repositories.

Das `/data/`-Verzeichnis ist unverzichtbar für zorg.ch, aber unterliegt nicht der Git-Codeversionierung!

```
/zorg-code/ <-- Cloned Git-Repo
  www/ <-- Cloned
  cron/ <-- Cloned
  keys/ <-- Cloned
  migration/ <-- Cloned
  scripts/ <-- Cloned
  data/ <-- this+ff CREATE!
      errlog/
      files/
      gallery/
          41/
      hz_maps/
      smartylib/
          cache/
          templates_c/
      tauschboerse/
      temp/
      upload/
      userimages/
```

### Berechtigungen richtig setzen
Vergiss nicht die korrekte Ownership und Berechtigungen auf alle Verzeichnisse & Files zu setzen!

**Ownership User & Group**
(`www-data` muss mit apache2 User & Group korrespondieren)

  chown www-data:www-data /zorg-code/www/*
  chown www-data:www-data /zorg-code/data/*

**`/www/`-Verzeichnis**

  $ chmod 755 $(find /zorg-code/www/ -type d)
  $ chmod 644 $(find /zorg-code/www/ -type f)

**`/data/files/`-Verzeichnis und Files**

  $ chmod 777 $(find /zorg-code/data/files/ -type d)
  $ chmod 644 $(find /zorg-code/data/files/ -type f)

**`/data/upload/`-Verzeichnis**

  $ chmod 755 /zorg-code/data/upload/

**`/smartylib/`-Verzeichnisse**
(Smarty braucht `777`)

  $ chmod 777 /zorg-code/data/smartylib/templates_c
  $ chmod 777 /zorg-code/data/smartylib/cache

## Apache Virtual Host konfigurieren
Es hat sich gezeigt, dass eine lokale zorg Installation nicht sauber funktioniert mit User Sessions (nach dem Login), solange kein virtueller Host im Apache Webserver definiert wurde. Deshalb machen wir das auch noch.

Du kannst dir entweder die [zorg Apache Configs aus dem entsprechenden GitHub Repo][17] ziehen und anwenden - oder Du gehst den Quick'n'dirty way:
1. Im lokalen Apache-Verzeichnis die Konfigurationsdatei "*httpd.conf*" in einem Editor öffnen
2. Sicherstellen, dass die Zeile `NameVirtualHost *:80` **aktiviert** ist (ggf. "#" am Anfang der Zeile entfernen!)
3. Folgenden VirtualHost in der "*httpd.conf*" Datei für den zorg www-Ordner festlegen:

```apache
  ServerAdmin admin@mail.com
  DocumentRoot "/pfad/zum/lokalen/apache/webroot/zorg-code/www/"
  ServerName localhost
  ServerAlias zorg.local *.zorg.local
```

### Hosts-Eintrag
Nun musst Du lokal noch den Hostnamen "*zorg.local*" in die hosts-Datei schreiben:
Unter macOS findet sich die Datei hier:

  /private/etc/hosts

Die Datei mit folgender neuen Zeile ergänzen:

  127.0.0.1    zorg.local


## zorg Datenbank
> Kontaktiere [kassiopaia][6], [[z]keep3r][6] oder [IneX][7] für einen aktuellen zorg DB-Dump

> Details siehe [MySQL database export, import and setup](migration/initial-setup/mysql-database.md)
1. Auf dem lokalen MySQL-Server eine neue Datenbank - z.B. `zorg` - anlegen und einen User mit sämtlichen Rechten (minimum aber Select, Insert, Update & Delete-Berechtigungen) dafür konfigurieren
2. Den zorg DB-Dump nun dort importieren

Damit zorg lokal mit dieser DB und dem gewählten User läuft, musst Du eine im [zorg www][9] eine MySQL-PHPdatei anlegen und konfigurieren:
1. unter `/zorg-code/www/includes/` erstelle eine Datei mit Bezeichnung `mysql_login.inc.local.php`
2. kopiere den folgenden Codes in die Datei und passe die Parameter entsprechend deiner Umgebung an:

```php
<?php
/** MySQL database login information */
define('MYSQL_HOST',	'127.0.0.1');
define('MYSQL_DBNAME',	'...');
define('MYSQL_DBUSER',	'...');
define('MYSQL_DBPASS',	'...');
```

### Deine lokale zorg Kopie sollte jetzt über den Webbrowser erreichbar sein! Teste das mit [http://zorg.local/](http://zorg.local)


# Am zorg Coding mitwirken
> BEVOR du Änderungen am Code vornimmst bzw. einchecken willst, immer zuerst den aktuellsten Code Stand mittels `git pull` ziehen!

## Applikationssettings lokal überschreiben
Gewisse Konfigurationen für zorg sind lokal für die Entwicklung anders, als die Live-Konfigurationen. Du kannst sämtliche Werte der `config.inc.php` sowie weitere speziellen Settings vor dem Laden aller weiteren Einstellungen "injecten".

Dazu benötigst du ein PHP-File names `development.config.php` unter dem Pfad: `/zorg-code/www/includes/`.

## Code Dokumentation
> Dokumentation nicht vergessen!
Damit auch anderen nachvollziehen können, was für Anpassungen am zorg Code Du vorgenommen hast und was die Intention dahinter ist, dokumentiere alles bitte entsprechend! (Fast) jeder Kommentar ist besser, als keiner... Um es Dir auch möglichst einfach zu machen, findest Du folgend entsprechende Schnipsel, die Du für die Dokumentation im Code adaptieren kannst.

Da wir dem [phpDoc Standard][10] folgen, können wir daraus nämlich auch laufend eine schöne Webseite der gesamten Dokumentation automatisch generieren lassen!

**WICHTIG**: Gross-/Kleinschreibung der phpDoc `@tag ...`-Tags bitte einhalten! (z.B. ist `@deprecated` ≠ `@DEPRECATED`)

#### Preview
Und so sieht unsere zorg Code Doku damit dann aus:
* [zorg Code phpDocumentor Doku](11)

#### Doku lokal generieren
Am besten mit der `phpDocumentor.phar`, dann über die CLI mit folgendem Befehl:

```console
$ php ./phpDocumentor.phar -c "/zorg-code/zorgcode_ghwiki.xml" --template="/path/to/phpDocumentor-Template-ghwiki" --cache-folder "./zorg-code-wiki-cache" --title "zorg Code Doku" --force
```

### Inline Kommentare
  /** Kurzbeschreibung einer einfachen Abfrage, Variable, usw. */

### Offene Arbeiten markieren
To-Dos in Codeblöcken können einfach im PHPDoc Block ergänzt werden mit folgendem Verweis:

```php
/**
* ...
* @TODO Hier habe ich noch etwas zu erledigen, und zwar... (Von wem, Datum)
* ...
*/

/** @TODO Hier ein kurzes inline To-do, und zwar... (Von wem, Datum) */

// TODO Hier ein kurzes inline To-Do aber ohne @...
```

### Notwendige Fixes / mögliche Fehlerquellen markieren
To-Dos mit höherer Prio, also etwas das unbedingt gefixt oder optimiert werden sollte, können mit folgendem Verweis markiert werden:

```php
/**
* ...
* @FIXME das hier muss refactored werden, weil es performance-intensiv ist (Von wem, Datum)
* ...
*/

/** @FIXME Hier ein kurzer Hinweis inline zum fixen, und zwar... (Von wem, Datum) */

// FIXME Hier ein kurzer Hinweis inline für einen notwendigen Fix aber ohne @...
```

### Links zu relevanten Webpages einfügen
In Doc-Blocks können relevante Links explizit hinzugefügt werden.

Wenn auf Files im zorg Code referenziert werden kann es sinnvoll sein den Link aus dem [zorg-code GitHub-Repo][18] zu verwenden:

```php
/**
* ...
* @link [URI] [description]
* @link https://github.com/zorgch/zorg-code/blob/master/README.md Dieses README enthält weitere Informationen
* ...
*/
```

### Referenzierungen auf andere Codestellen
Nebst (externen) Links können in Doc-Blocks auch andere, relevante Codestellen direkt referenziert werden:

```php
/**
* ...
* @see \Namespace\Klasse::funktion() Die Funktion aus der Klasse XY wird hier verwendet
* @see funktion() Diese Funktion wird hier auch verwendet
* ...
*/
```

Wenn man allerdings den Tag `@uses` oder `@used-by` verwendet, wird in der Dokumentation eine Relation zwischen den genannten Elementen hergestellt:

```php
/**
* ...
* @uses [file | "FQSEN"] [<description>]
* @uses README.md Dieses README enthält weitere Informationen
* ...
*/
```

### Nicht mehr benötigte / veraltete Stellen markieren
Wenn eine Codestelle oder -elemente, insbesondere Funktionen, Klassen, Konstanten,... - aber auch ganze Files - nicht mehr benötigt werden, bitte als `@deprecated` markieren:

```php
/**
* Titel meiner Funktion
*
* ...
* @deprecated 2.0 Bis Version 2.0 wurde diese Funktion noch gebraucht
* ...
*/
function MeineAlteFunktion($params=null)
{
  ...
```

### Files dokumentieren
```php
<?php
/**
* Kurzbeschreibung des Files
*
* Lange Beschreibung wieso es diese
* _tolle_ File denn gibt. Ich kann dazu mehrere
* Zeilen verwenden, mit **markdown** formatierungen.
* Natürlich müssen nicht alle Parameter
* für die Doku verwendet werden.
*
* @package zorg[\subpackage] (z.B. "Addle", "Events", o.ä.)
* @author Ursprünglicher Autor
* @author Dein-Name
*/
...
```

#### File includes dokumentieren
Quelle nur auf [phpdoc.de](http://www.phpdoc.de/kongress/include.html) gefunden.

```php
<?php
/**
* Kurzbeschreibung einer eingebundenen Datei (oder mehreren).
* @include	datei.inc.php Funktion: _include_ | Required: nein
* @include	datei2.inc.php Funktion: _require_once_ | Required: ja
*/
include_once( dirname(__FILE__) . '/datei.inc.php');
require_once( dirname(__FILE__) . '/datei2.inc.php');
...
```

### Konstanten dokumentieren
```php
/**
* Beschreibung der ersten Konstante
* @const WEBROOT Contains the absolute path to the Webroot directory
*/
if (!defined('WEBROOT')) define('WEBROOT', '/', true);

/**
* Beschreibung der zweiten Konstante
* @const SITEURL Contains the root URL of my website
*/
if (!defined('SITEURL')) define('SITEURL', 'zorg.ch', true);
...
```

### Klassen dokumentieren
```php
/**
* Titel meiner Klasse
*
* Lange Beschreibung meiner tollen
* Klasse. Hier kann ich auf mehreren
* Zeilen schreiben, mit _markdown_ formatierungen.
* Natürlich müssen nicht alle Parameter
* für die Doku verwendet werden.
*
* @package zorg[\subpackage] (z.B. "Events", "Bugtracker", "Games\HuntingZ", o.ä.)
* @author Ursprünglicher Autor
* @author Dein Name
* @version 1.0 (aktuelle Version der Klasse)
* @since 1.0 `23.05.2003` `author` Klasse hinzugefügt
* @since x.x `dd.mm.yyyy` `author` Klassenänderungen/Change-log der Klasse
*/
class MeineKlasse
{
  ...
}
```

#### Variablen in Klassen dokumentieren
```php
/**
* Beschreibung der ersten Variable mit Angabe des Typs
* @var array $meinevar1 Beschreibung...
*/
...
/**
* Beschreibung der zweiten Variable mit Angabe des Typs
* @var string $meinevar2 Beschreibung...
*/
...
```

#### Funktionen dokumentieren
```php
/**
* Titel meiner Funktion
*
* Lange Beschreibung meiner tollen
* Funktion. Hier kann ich auf mehreren
* Zeilen **beschreiben**, was ich will.
*
* @author Dein-Name
* @version 1.0 (aktuelle Version der Funktion)
* @since 1.0 `23.05.2003` `author` Funktion hinzugefügt
* @since x.x `dd.mm.yyyy` `author` Funktionsänderungen/Change-log der Funktion
*
* @param integer $user_id Eine User-ID muss der Funktion übergeben werden
* @param string|array $params Parameter als String oder Array für meine Funktion - default: null
* @global array Datenbank-Informationen in {$db}
* @global array User-Informationen in {$user}
* @return string/boolean/integer/array
*/
function MeineFunktion($user_id, $params=null)
{
  global $db, $user;
  
  $code = '...';
  
  /** Kurzer inline Kommentar */
  if ($code === '...') echo 'Yarak';
}
```

#### Alleinstehende Funktionen (Klassen-unabhängig)
Wenn eine Funktion AUSSERHALB einer Klasse geschrieben wird, bitte noch die Package-Informationen im phpDoc Block ergänzen (bei Funktionen innerhalb von Klassen ist dieser Kontext bereits gegeben):

  * @package zorg[\subpackage] (z.B. "Layout", "Games\Addle", o.ä.)


# Neusten Codestand deployen
## Pull-Request mit Code-Änderungen erstellen
Direkt auf GitHub lassen sich einfach Pull-Requests auf den `master`-Branch erzeugen, um alle neuen Codeänderungen zusammenzuführen:

* [zorg Code Pull Requests](https://github.com/zorgch/zorg-code/pulls)

## Deployments
Der zorg Code kann inzwischen bequem über ein Deployment-Interface ausgeliefert und aktualisiert werden. Falls Migrationsscripts (aus `/migration/*` notwendig sind, können diese ebenfalls dort ausgeführt werden).

Stelle auf jeden Fall sicher, dass deine Codeänderungen auf dem `develop`-Branch zwecks Testing eingecheckt sind. So können sie auf den zorg Construct (Test-Instanz) deployed und verifiziert werden.

Für produktive Auslieferung sollen die Changes via Pull Request auf den `master`-Branch übernommen worden sein.

> Wende dich an [kassiopaia][6] oder [IneX][7] für ein Deployment auf Construct oder Live!

**DONE** - die Änderungen müssten jetzt auch auf [zorg.ch][8] aktiv sein.

# **Initiales Setup**: Repo EINMALIG auf den Server runterladen
### Git installieren

  $ apt-get update
  $ apt-get install git

### apache2 konfigurieren
Jetzt noch apache2 konfigurieren mit den notwendigen Konfigurationsdateien:

* siehe [zorgch/zorg-apache-configs][17]
* Testen der Konfiguration mittels:

  $ apachectl configtest

**That's it** - zorg.ch sollte nun laufen & ausgeliefert werden.

[1]: https://www.mamp.info/ "MAMP for macOS"
[2]: https://www.wampserver.com/ "WAMP for Windows"
[3]: https://www.turnkeylinux.org/lampstack "LAMP for Linux"
[4]: https://github.com/orgs/zorgch/teams/kenner "zorg Verein - Kenner"
[5]: https://github.com/fbentele "Flo"
[6]: https://github.com/raschle "Nico"
[7]: https://github.com/oliveratgithub "Oli"
[8]: https://zorg.ch/ "zorg.ch"
[9]: https://github.com/zorgch/zorg-code/tree/master/www/includes "/zorg-code/www/includes"
[10]: https://phpdoc.org/ "phpDocumentor website"
[11]: https://github.com/zorgch/zorg-code/wiki "zorg Code phpDocumentor Doku"
[13]: https://zorg.ch/bug/733 "Bug #733 - PHP Version updaten"
[14]: https://github.com/orgs/zorgch/projects/2 "zorg Coding Roadmap - GitHub Project"
[15]: https://www.sitepoint.com/quick-tip-how-to-permanently-change-sql-mode-in-mysql/ "How to Permanently Change SQL Mode in MySQL 5.7"
[16]: https://github.com/zorgch/zorg-code/releases "zorg Code Releases"
[17]: https://github.com/zorgch/zorg-apache-configs "zorgch/zorg-apache-configs"
[18]: https://github.com/zorgch/zorg-code "zorg-code GitHub Repository"
