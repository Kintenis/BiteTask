## Set-up

1. Make sure you have Docker and Docker Compose installed on your system
2. Clone this repository
3. Navigate to the repository directory
4. Run `docker compose up -d`
5. Get your ipstack.com API key and put it inside IP_STACK_AUTH_KEY parameter. (.env)
6. Go to php docker container and run `composer install`
7. Go to php docker container and run `php bin/console doctrine:migrations:migrate`
8. Run unit tests by going to php docker container and running `php bin/phpunit`
9. Access the API at http://localhost:8080/api/doc