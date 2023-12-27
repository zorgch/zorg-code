# zorg on Docker
---

## Docker configs
Edit the file: `.env.docker`

#### Setup on macOS
(The following steps are copied from [this online documentation](https://reece.tech/posts/osx-docker-performance/))
* **Recommendation**: use [OrbStack](https://orbstack.dev/) instead of Docker Desktop for Mac!

## Build the Docker container & start the services
Start container with all services.

* in "detached mode" - without interative log in the Shell

```
cd /path/to/zorg-code/
docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up -d
```

* or with an interactive log in the Shell

```
cd /path/to/zorg-code/
docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up
```

## Usage
---
### Service configurations
#### MySQL connection config
The MySQL database host name is `zorg-db` and needs be added to the PHP environment config file `/www/.env`.

#### sendmail SMTP config
Edit the msmtprc config file in the `./Docker/sendmail/` directory and replace the following placeholders with real values:
* SMTP_HOST => mail.mymailserver.com
* SMTP_EMAIL => myemail@mymailserver.com
* SMTP_PASSWORD => password for your SMTP_EMAIL account

Further details on the sendmail / msmtprc integration can be found here: [Send email on testing docker container with php and sendmail](https://stackoverflow.com/a/63977888/5750030)

### Show the website
[http://localhost/](http://localhost/)

…or with a hosts entry pointing to `127.0.0.1` and SSL: [https://zorg.local/](https://zorg.local/)

### Use PHPMyAdmin to manage the database
[http://localhost:8080/](http://localhost:8080/)

…or with a hosts entry pointing to `127.0.0.1`: [http://zorg.local:8080/](http://zorg.local:8080/)

* **Server**: use the Docker's `zorg-db`-service hostname or IP-address
* **Username**: use the defined `MYSQL_USER`-environment value
* **Password**: use the defined `MYSQL_PASSWORD`-environment value

#### Using a pre-existing local database
The best way is to import an SQL-dump using the phpmysql Docker service at [http://localhost:8080/](http://localhost:8080/).

Alternatively the path to a local database folder can be provided by overriding the ENV var `MYSQL_LOCAL_DATABASE_PATH`:
```
MYSQL_LOCAL_DATABASE_PATH=/path/to/my/mysql57 docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up -d
```

#### Fix possible "Tablespace missing"-errors
To fix the MySQL-Error 1812 `Tablespace is missing for table zooomclan . <table-name>` try the following command per affected `<table-name>`:

```ALTER TABLE zooomclan.<table-name> IMPORT TABLESPACE```


## Docker services inspection
Find IP of a container service (can also be seen in the network details)

`docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' SERVICENAME`

Inspect all running Docker services

`docker ps`

Inspect all container service details

`docker container inspect SERVICENAME`

Inspect a container's network details

`docker network inspect CONTAINER_NETWORKNAME`

Execute a shell command on a container service

`docker exec -it SERVICENAME ls -la`

* Example: show apache2's `000-default.conf` file:

`docker exec -it zorg-web cat /etc/apache2/sites-available/000-default.conf`

Enter into interactive shell mode for a container service

`docker exec -it SERVICENAME sh`

List all Environment Variables for a container service

`docker exec SERVICENAME env`

### docker-sync inspection
!! Refresh docker-sync after updating the `docker-compose.yml`-file

`docker-sync clean`

Inspect running docker-sync services:

`docker volume ls | grep -sync`

### sendmail Logfile
Inspect the logfile for sendmail / msmtprc:

`docker exec -it zorg-web cat /var/log/sendmail.log`


## Archive / Deprecated
### Using docker-sync (not recommended!)
`docker-sync` greatly improves the performance of synced volumes from the local file system to Docker, giving a nearly live-performance for read/write operations.

##### Install docker-sync

```
gem install --user-install docker-sync
brew install fswatch
brew install unison
brew install eugenmayer/dockersync/unox
```

#### Configuring docker-sync
Docker sync requires a valid configuration file (docker-sync.yaml), the below file creates a named volume for Docker called osx-sync and mounts the local macOS directory.

Add `docker-sync` to your $PATH using `nano ~/.zshrc`

```
if which ruby >/dev/null && which gem >/dev/null; then
  PATH="$(ruby -r rubygems -e 'puts Gem.user_dir')/bin:$PATH"
fi
```

Now run `source ~/.zshrc` to apply the $PATH settings.

##### Add `docker-sync.yml` to `/Docker/` dir
Here's an example `docker-sync` YAML configuration:

```yaml
version: "2"
options:
    config_path: './../'
    compose-file-path: './docker-compose.yml'
    verbose: true
syncs:
# sync_strategy: see https://docker-sync.readthedocs.io/en/latest/getting-started/configuration.html#sync-strategy
    zorg-web-root-git-sync:
        src: './.git'
        sync_strategy: 'unison'
    zorg-web-root-data-sync:
        src: './data'
        sync_strategy: 'unison'
        #sync_excludes: [ ]
    zorg-web-root-vendor-sync:
        src: './vendor'
        sync_strategy: 'unison'
        # sync_host_ip: 'auto'
        # sync_host_port: 10873
        sync_excludes: [ ]
    zorg-web-root-www-sync:
        src: './www'
        sync_strategy: 'unison'
        sync_excludes: [ ]
    zorg-db-mysql-sync:
        src: './Docker/mysql57'
        sync_strategy: 'unison'
        sync_excludes: [ ]

```

#### Build the Docker container with docker-sync
Start container with all services.

* in "detached mode" - without interative log in the Shell

```
cd /path/to/zorg-code/
docker-sync start -c ./Docker/docker-sync.yml
docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up -d
```
