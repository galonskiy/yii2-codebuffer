CodeBuffer
==========
This extension for Yii framework 2.0.

CodeBuffer - component for generated and validation SMS, e-mail and other codes.

[![Build Status](https://travis-ci.org/galonskiy/yii2-codebuffer.svg?branch=master)](https://travis-ci.org/galonskiy/yii2-codebuffer)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Run

```
composer require galonskiy/yii2-codebuffer "*"
```

or add

```
"galonskiy/yii2-codebuffer": "*"
```

to the require section of your `composer.json` file.

Migration
--------------
Before you can go on you need to create those tables in the database.

```
php yii migrate --migrationPath=@vendor/galonskiy/yii2-codebuffer/migrations
```

Usage as component
------------------

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

and use it in your code by:

```php
Yii::$app->codebuffer->generate('XXX', 'XXX');

```
```php
Yii::$app->codebuffer->validate('XXX', 'XXX', 'CODE');
```

Usage as class
--------------

Use it in your code by:

```php
(new \galonskiy\codebuffer\CodeBuffer)->generate('XXX', 'XXX');
```

