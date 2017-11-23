CodeBuffer
==========
CodeBuffer - description

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist galonskiy/yii2-codebuffer "*"
```

or add

```
"galonskiy/yii2-codebuffer": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
	'components' => [
        ...
        'codebuffer' => [
            'class' => '\galonskiy\codebuffer\CodeBuffer'
        ]
        ...
    ],```