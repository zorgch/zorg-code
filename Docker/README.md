# zorg on Docker
---

## Docker configs
Edit the file: `.env.docker`

## Using docker-sync
`docker-sync` greatly improves the performance of synced volumes from the local file system to Docker, giving a nearly live-performance for read/write operations.

### On macOS
(The following steps are copied from [this online documentation](https://reece.tech/posts/osx-docker-performance/))

#### Install docker-sync

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

## Build the Docker container and docker-sync
Start container with all services.

* in "detached mode" - without interative log in the Shell

```
cd /path/to/zorg-code/
docker-sync start -c ./Docker/docker-sync.yml
docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up -d
```

* or with an interactive log in the Shell

```
cd /path/to/zorg-code/
docker-sync-stack start -c ./Docker/docker-sync.yml
docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up
```

### Using a pre-existing local database

```
MYSQL_LOCAL_DATABASE_PATH=/path/to/my/mysql57 docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up -d
```

#### Fix possible "Tablespace missing"-errors
To fix the MySQL-Error 1812 `Tablespace is missing for table zooomclan . <table-name>` try the following command per affected `<table-name>`:

```ALTER TABLE zooomclan.<table-name> IMPORT TABLESPACE```


Usage
---
#### MySQL connection config
Add the Docker's `zorg-db`-service IP-address to the file: `/www/.env`

#### Show the website
[http://localhost/](http://localhost/)

…or with a hosts entry pointing to `127.0.0.1` and SSL: [https://zorg.local/]

#### Use PHPMyAdmin to manage the database
[http://localhost:8080/](http://localhost:8080/)

…or with a hosts entry pointing to `127.0.0.1`: [http://zorg.local:8080/]

* **Server**: use the Docker's `zorg-db`-service IP-address
* **Username**: use the defined `MYSQL_USER`-environment value
* **Password**: use the defined `MYSQL_PASSWORD`-environment value

#### Docker services inspection
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

#### docker-sync inspection
!! Refresh docker-sync after updating the `docker-compose.yml`-file

`docker-sync clean`

Inspect running docker-sync services:

`docker volume ls | grep -sync`
