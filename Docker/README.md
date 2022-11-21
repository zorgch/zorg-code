# zorg on Docker
---

## Docker configs
Edit the file: `.env.docker`

## Build the Docker container
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

### Using a pre-existing local database

```
MYSQL_LOCAL_DATABASE_PATH=/path/to/my/mysql57 docker compose --project-directory ./ --file ./Docker/docker-compose.yml --env-file ./Docker/.env.docker up -d
```


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
