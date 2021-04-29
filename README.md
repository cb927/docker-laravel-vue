# Weld Alpha

## Urls

Urls used in the project:
| Name | Url |
|-------------- | -------------- |
| **front** | [weldapp.localhost](http://weldapp.localhost) |
| **api** | [api.weldapp.localhost](http://api.weldapp.localhost) |
| **phpmyadmin** | [phpmyadmin.weldapp.localhost](http://phpmyadmin.weldapp.localhost) |
| **admin panel** | [api.weldapp.localhost/nova](http://api.weldapp.localhost/nova) |

## Setup

To get started, make sure you have [Docker installed](https://docs.docker.com/) on your system and [Docker Compose](https://docs.docker.com/compose/install/), and then clone this repository.

1. Clone this project:

   ```sh
   git clone [YOUR_REPO_URL_HERE]
   ```

2. Inside the root directory and Generate your own `.env` to docker compose with the next command:

   ```sh
   cp .env.example .env
   ```

2. Inside the folder `api` and Generate your own `.env` to laravel with the next command:

   ```sh
   cd api
   cp .env.example .env
   ```

3. Install pakages by composer

   ```sh
   docker-compose run composer install
   ```

4. Generate database tables

   ```sh
   docker-compose run artisan migrate
   ```

5. Populate data to database tables

   ```sh
   docker-compose run artisan db:seed
   ```

6. Run the project whit the next commands:

   ```sh
   docker-compose up
   ```

---

## Troubleshot

 If you get folder permission error, run following command

   ```sh
   chmod -R 777 api/storage/*
   ```