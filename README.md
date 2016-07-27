# Laravel Meta data

Add metadata to laravel models

## Requirements

- PHP >=5.4


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


## Usage

### Basic Example

First you need to go to your model and use the HasManyMetaDataTrait:

```php
// E.x. User.php
// add this before the class declaration
use AstritZeqiri\Metadata\Traits\HasManyMetaDataTrait;

// after the class declaration add this code snippet:
use HasManyMetaDataTrait;
```

Updating a meta data entry:

```php
// get the instance
$user = \App\User::first();

// update a metadata if it exists else add a new one
$user->update_meta("meta_key", "meta_value");

```

Get meta data entry:

```php

// get the instance
$user = \App\User::first();

// get a metadata object with a given key
$user->get_meta("meta_key");

// if the second parameter is true it returns onl the value
$user->get_meta("meta_key" ,true);
```
