# Assets versions

## Requirments

PHP >= 8.0


## Install

`composer require phant/assets-versions`


## Basic usage

Add this code in your initialization of your application :

```php
use Phant\AssetsVersions\AssetsVersions;

$assetsVersions = new AssetsVersions(
	'public/', // path to be processed
	[ 'css', 'js' ], // extensions to be processed
	[ 'node_modules/' ] // path to be ignored in path to be processed
);
```

And this code when calling your assets :

```
<link rel="stylesheet" href="<?= $assetsVersions->of( 'styles/main.css' ) ?>"/>
<script src="<?= $assetsVersions->of( 'lib/init.js' ) ?>"></script>
```


## Exemple with a cache manager

```php
use Phant\AssetsVersions\AssetsVersions;
use Phant\Cache\SimpleCache;

$assetsVersions = new AssetsVersions(
	'public/', // path to be processed
	[ 'css', 'js' ], // extensions to be processed
	[ 'node_modules/' ], // path to be ignored in path to be processed
	new SimpleCache( '/my-cache-path/', 'my-app-cache', 30 * 86400 ) // cache adapter
);
```


You can generate assets versions cache with this method :

```
$assetsVersions->generate();
```
