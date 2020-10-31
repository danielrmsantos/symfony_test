[![Build Status](https://travis-ci.com/danielrmsantos/symfony_test.svg?branch=master)](https://travis-ci.com/danielrmsantos/symfony_test)
![GitHub repo size](https://img.shields.io/github/repo-size/danielrmsantos/symfony_test)
![GitHub last commit](https://img.shields.io/github/last-commit/danielrmsantos/symfony_test)
### Symfony Test
Symfony 4 + PHP 7.4 + MySQL + Docker

### Setup
Run this commands
```
docker-compose up -d
docker-compose exec php bash
cd sf4
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Postman Collection
[Import this collection into postman with available endpoints](symfony_test.postman_collection.json)

## Author

**Daniel Santos**

* [github/danielrmsantos](https://github.com/danielrmsantos)