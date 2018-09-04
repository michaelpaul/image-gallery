### Install

Install dependencies

    docker-compose run --rm web composer install    

Database/Migrations

    docker-compose run --rm web php bin/console doctrine:migrations:migrate --no-interaction

Start the containers

    docker-compose up -d

Get your [Cloudinary](https://cloudinary.com/console) URL and set it in the .env file.

You can then access the application through the following URL:

    http://127.0.0.1:5000
