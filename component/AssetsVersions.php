<?php
declare(strict_types=1);

namespace Phant\AssetsVersions;

use Psr\SimpleCache\CacheInterface;
use Phant\AssetsVersions\Exception\{
	PathToBeProcessedDoesNotExist,
};
use Phant\AssetsVersions\DataStructure\{
	AssetVersion,
	AssetVersionCollection,
};

class AssetsVersions
{
	const CACHE_ITEM = 'assets-versions';
	
	protected string $pathToBeProcessed;
	protected array $extensionsToBeProcessed;
	protected ?array $pathsToBeIgnored;
	protected ?CacheInterface $cacheAdapter;
	
	private AssetVersionCollection $assetVersionCollection;
	
	public function __construct(
		string $pathToBeProcessed,
		array $extensionsToBeProcessed = [ 'css', 'js' ],
		?array $pathsToBeIgnored = null,
		?CacheInterface $cacheAdapter = null
		)
	{
		if ( !is_dir( $pathToBeProcessed ) ) {
			throw new PathToBeProcessedDoesNotExist;
		}
		
		$this->pathToBeProcessed = $pathToBeProcessed;
		$this->extensionsToBeProcessed = array_map( 'strtolower', $extensionsToBeProcessed );
		$this->pathsToBeIgnored = $pathsToBeIgnored;
		$this->cacheAdapter = $cacheAdapter;
		
		$this->assetVersionCollection = new AssetVersionCollection();
		
		$this->loadFromCache();
	}
	
	public function of( string $assetPath ): string
	{
		$assetVersion = $this->assetVersionCollection->findFromAssetPath( $assetPath );
		
		if ($assetVersion) {
			return (string) $assetVersion;
		}
		
		$assetVersion = $this->getFromPath( $assetPath );
		
		if ($assetVersion) {
			$this->assetVersionCollection->add( $assetVersion );
			$this->saveToCache();
			return (string) $assetVersion;
		}
		
		return $assetPath;
	}
	
	public function generate(): void
	{
		$assetPathList = $this->getAssetPathList();
		
		foreach ( $assetPathList as $assetPath ) {
			$assetVersion = $this->getFromPath( $assetPath );
			$this->assetVersionCollection->add( $assetVersion );
		}
		
		$this->saveToCache();
	}
	
	private function loadFromCache(): void
	{
		if ( !$this->cacheAdapter ) return;
		
		$assetVersionCollection = $this->cacheAdapter->get( self::CACHE_ITEM );
		
		if ( !is_a( $assetVersionCollection, get_class( $this->assetVersionCollection ) ) ) {
			return;
		}
		
		$this->assetVersionCollection = $assetVersionCollection;
	}
	
	private function saveToCache(): void
	{
		if (!$this->cacheAdapter) return;
		
		$this->cacheAdapter->set( self::CACHE_ITEM , $this->assetVersionCollection );
	}
	
	private function getFromPath( string $assetPath ): ?AssetVersion
	{
		$assetVersion = $this->getAssetVersionFromGitRevisions( $assetPath );
		
		if ( $assetVersion ) {
			return $assetVersion;
		}
		
		$assetVersion = $this->getAssetVersionFromFileUpdateTime( $assetPath );
		
		if ( $assetVersion ) {
			return $assetVersion;
		}
		
		return null;
	}
	
	private function getAssetVersionFromGitRevisions( string $assetPath ): ?AssetVersion
	{
		$command = sprintf( 'git log --oneline %s | wc -l', escapeshellarg( $this->pathToBeProcessed . $assetPath ) );
		$revision = exec( $command );
		
		if ( !$revision ) {
			return null;
		}
		
		return new AssetVersion( $assetPath, AssetVersion::TYPE_VERSION, (int) $revision );
	}
	
	private function getAssetVersionFromFileUpdateTime( string $assetPath ): ?AssetVersion
	{
		$modificationTime = filemtime( $this->pathToBeProcessed . $assetPath );
		
		if ( !$modificationTime ) {
			return null;
		}
		
		return new AssetVersion( $assetPath, AssetVersion::TYPE_MODIFICATION_TIME, (int) $modificationTime );
	}
	
	private function getAssetPathList( string $subpath = '' ): array
	{
		$assetPathList = [];
		
		$dh = opendir( $this->pathToBeProcessed . $subpath );
		
		while ( $entry = readdir( $dh ) ) {
			
			if ( $entry == '.' || $entry == '..' ) {
				continue;
			}
			
			$entryPath = $subpath . $entry;
			
			if ( is_dir( $entryPath )) {
				$entryPath.= '/';
				
				if (in_array( $entryPath, $this->pathsToBeIgnored ) ) {
					continue;
				}
				
				$assetPathList = array_merge( $assetPathList, $this->getAssetPathList( $entryPath ) );
				
				continue;
			}
			
			if ( !empty( $this->extensionsToBeProcessed ) && !in_array( strtolower( pathinfo( $entryPath, PATHINFO_EXTENSION ) ), $this->extensionsToBeProcessed ) ) {
				continue;
			}
			
			$assetPathList[] = $entryPath;
		}
		
		closedir($dh);
		
		return $assetPathList;
	}
}
