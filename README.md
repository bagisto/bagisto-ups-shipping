### 1. Introduction:

UPS Shipping module provides UPS Shipping methods for shipping the product. By using this, you can provide UPS (United Parcel Service) shipping. UPS is widely acknowledged as a world-class company and now it is with the Bagisto.
This module works with the Bagisto core Package. To use this module you must have installed Bagisto.

It packs in lots of demanding features that allows your business to scale in no time:

* The admin can enable or disable the UPS Shipping method.

* The admin can set the UPS shipping method name that will be shown from the front side.

* The admin can define the allowed methods and weight units.

* Dynamic shipping method for freight calculation.

### 2. Requirements:

* **Bagisto**: v0.1.6 or higher.

### 3. Installation:

* Unzip the respective extension zip and then merge "packages" folders into project root directory.
* Goto config/app.php file and add following line under 'providers'

~~~
Webkul\UpsShipping\Providers\UpsShippingServiceProvider::class
~~~

* Goto composer.json file and add following line under 'psr-4'

~~~
"Webkul\\UpsShipping\\": "packages/Webkul/UpsShipping/src"
~~~

* Run these commands below to complete the setup

~~~
composer dump-autoload
~~~

~~~
php artisan route:cache
~~~

~~~
php artisan config:clear
~~~

~~~
php artisan vendor:publish

-> Press 0 and then press enter to publish all assets and configurations.
~~~

> now execute the project on your specified domain.