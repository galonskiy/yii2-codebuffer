CodeBuffer
==========
This extension for Yii framework 2.0.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist galonskiy/yii2-codebuffer "*"
```

or add

```
"galonskiy/yii2-codebuffer": "*"
```

to the require section of your `composer.json` file.

Before you can go on you need to create those tables in the database.:

```
php yii migrate --migrationPath=@vendor/galonskiy/yii2-codebuffer/migrations
```

Usage
-----

Once the extension is installed, simply paste it in your config by:

```php
    ...
    'components' => [
        ...
        'codebuffer' => [
            'class' => '\galonskiy\codebuffer\CodeBuffer'
        ]
        ...
    ]
    ...
```
