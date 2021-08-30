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


Exemple with Symfony FilesystemAdapter :

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Phant\AssetsVersions\AssetsVersions;


// cache

$cache = new FilesystemAdapter( 'my-app-cache' );


// assets versions

$callbackLoadCache = function() use ( $cache ): ?array {
	return $cache->getItem( $item )->get( 'assets-versions' );
};

$callbackSaveCache = function( ?array $assetVersionCollection ) use ( $cache ) {
	$cacheItem = $cache->getItem( 'assets-versions' );
	$cacheItem->set( $assetVersionCollection );
	$cacheItem->expiresAfter( 30 * 86400 ); // 30 days
	$cache->save( $cacheItem );
};

$assetsVersions = new AssetsVersions(
	'public/', // path to be processed
	[ 'css', 'js' ], // extensions to be processed
	[ 'node_modules/' ], // path to be ignored in path to be processed
	$callbackLoadCache, //callback load cache
	$callbackSaveCache //callback save cache
);
```


You can generate assets versions cache with this method :

```
$assetsVersions->generate();
```
