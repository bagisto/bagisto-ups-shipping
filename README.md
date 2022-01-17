# Introduction

Bagisto UPS Shipping add-on provides UPS Shipping methods for shipping the product. By using this, you can provide UPS (United Parcel Service) shipping.

It packs in lots of demanding features that allows your business to scale in no time:

- The admin can enable or disable the UPS Shipping method.

- The admin can set the UPS shipping method name that will be shown from the front side.

- The admin can define the allowed methods and weight units.

- Dynamic shipping method for freight calculation.

- Tax rate can be calculated based on UPS shipping

## Requirements:

- **Bagisto**: v1.3.3

## Installation :
- Run the following command
```
composer require bagisto/bagisto-ups-shipping
```

- Run these commands below to complete the setup
```
composer dump-autoload
```

```
php artisan route:cache
php artisan optimize
php artisan vendor:publish
```
-> Press 0 and then press enter to publish all assets and configurations.

> now execute the project on your specified domain.
