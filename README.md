# laravel-basic-accountant


## Installing
Via Composer

``` bash
composer require bewarhizirvan/laravel-basic-accountant
```

Via Git
``` bash
git clone https://github.com/bewarhizirvan/laravel-basic-accountant.git
cd laravel-basic-accountant
composer update
cp .env.example .env
php artisan key:generate
```

Update .env Data
``` bash
php artisan migrate:refresh --seed
```
