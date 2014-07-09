# Zorg Code - Checkout und lokale Instanz inkl. Datenbank aufsetzen

## VORAUSSETZUNGEN

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

        git clone https://bitbucket.org/rnatau/zorg.ch.git /pfad/zum/lokalen/apache/webroot/

3. Im Apache Web-Root (auch "*htdocs/*" oder "*www/*") befindet sich jetzt eine Kopie des Zorg www-Verzeichnis mit sämtlichen Dateien von Zorg.

*Bevor Zorg aber lokal angezeigt werden kann muss zuerst noch [die Datenbank](#z-db-setup) eingerichtet werden!*


## Pull-Request des Zorg Repository erstellen (für lokale Entwicklung)

1. Erstelle im Zorg Repository einen neuen [Pull Request](https://bitbucket.org/rnatau/zorg.ch/pull-request/new)
2. Alternativ geht das auch via Terminal/Git Bash mit folgendem Befehl:

        git pull https://bitbucket.org/rnatau/zorg.ch.git

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



[1]: https://confluence.atlassian.com/pages/viewpage.action?pageId=269981802 "Set up Git and Mercurial (Mac OS X)"
[2]: https://confluence.atlassian.com/display/BITBUCKET/Set+up+Git+and+Mercurial "Set up Git and Mercurial (Windows)"
[3]: https://confluence.atlassian.com/pages/viewpage.action?pageId=269982882 "Set up Git and Mercurial (Linux)"
[4]: https://bitbucket.org/rnatau/zorg.ch "zorg.ch - Bitbucket"
[5]: http://www.zorg.ch/profil.php?user_id=8 "[z]bert Profil"
[6]: http://www.zorg.ch/profil.php?user_id=52 "[z]keep3r Profil"
[7]: http://www.zorg.ch/profil.php?user_id=117 "IneX Profil"
[8]: https://confluence.atlassian.com/pages/viewpage.action?pageId=271942986 "Fork a Repo, Compare Code, and Create a Pull Request (Mac OSX/Linux)"
[9]: https://bitbucket.org/rnatau/zorg.ch/src/3dd86099c6445a606c4fa81882f06b6567633baf/www/includes/mysql_login.inc.php?at=master "mysql_login.inc.php"