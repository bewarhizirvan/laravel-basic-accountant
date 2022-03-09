# laravel-basic-accountant

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]


## Installing
Via Composer

``` bash
composer create-project bewarhizirvan/laravel-basic-accountant
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

[ico-version]: https://img.shields.io/packagist/v/bewarhizirvan/laravel-basic-accountant.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/bewarhizirvan/laravel-basic-accountant.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/bewarhizirvan/laravel-basic-accountant
[link-downloads]: https://packagist.org/packages/bewarhizirvan/laravel-basic-accountant
