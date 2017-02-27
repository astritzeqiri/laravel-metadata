# Laravel Meta data

Add metadata to laravel models

## Requirements

- PHP >=5.4
- Laravel >= 5.0


## Installation

Add laravel-metadata to your composer.json file:

```json
"require": {
    "astritzeqiri/laravel-metadata": "~1.0"
}
```

Get composer to install the package:

```
$ composer require astritzeqiri/laravel-metadata
```

### Registering the Package

Register the service provider within the `providers` array found in `app/config/app.php`:

```php
'providers' => array(
    // ...
    AstritZeqiri\Metadata\LaravelMetaDataServiceProvider::class
)
```

If you want you can add an alias to the MetaData model within the `aliases` array found in `app/config/app.php`:


```php
'aliases' => array(
    // ...
    'MetaData' => AstritZeqiri\Metadata\Models\MetaDada::class,
)
```

Then you need to publish the migration files:

```
$ php artisan vendor:publish --provider="AstritZeqiri\Metadata\LaravelMetaDataServiceProvider"
```

And then run the migration:

```
$ php artisan migrate
```

# Usage

### Basic Example

First you need to go to your model and use the HasManyMetaDataTrait:

```php
// E.x. User.php
// add this before the class declaration
use AstritZeqiri\Metadata\Traits\HasManyMetaDataTrait;

// after the class declaration add this code snippet:
use HasManyMetaDataTrait;
```

### Updating a meta data entry:

```php
// get the instance
$user = \App\User::first();

// update a metadata if it exists else add a new one
$user->update_meta("meta_key", "meta_value");

```

### Get meta data entry:

```php

// get the instance
$user = \App\User::first();

// get a metadata object with a given key
$user->get_meta("meta_key");

// if the second parameter is true it returns only the value
$user->get_meta("meta_key", true);
```

### Delete meta data entry:

```php

// get the instance
$user = \App\User::first();

// delete a metadata entry with a given key
$user->delete_meta("meta_key");

// delete all metadatas of a user
$user->delete_all_metas();

```


### Filter users by meta_data:

```php


// Search by only one meta data.
$users = \App\User::metaQuery('hair_color', 'red')->get();
// filter the users that have red hair color

// Search by many meta data.
$users = \App\User::metaQuery(array(
	array('key' => 'hair_color', 'value' => 'red'),
	array('key' => 'phone_number', 'value' => '%111%', 'compare' => "LIKE")
), "OR")->get();
// filter the users that have red hair color
// or that their phone_number contains '111'

```
