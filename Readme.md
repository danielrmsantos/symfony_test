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