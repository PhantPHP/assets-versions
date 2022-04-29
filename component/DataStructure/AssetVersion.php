<?php
declare(strict_types=1);

namespace Phant\AssetsVersions\DataStructure;

final class AssetVersion
{
	const TYPE_VERSION = 'ver';
	const TYPE_MODIFICATION_TIME = 'lmod';
	
	public string $assetPath;
	public string $type;
	public int $value;
	
	public function __construct( string $assetPath, string $type, int $value )
	{
		$this->assetPath = $assetPath;
		$this->type = $type;
		$this->value = $value;
	}
	
	public function __toString()
	{
		return $this->assetPath . '?' . http_build_query([
			$this->type => $this->value
		]);
	}
}
