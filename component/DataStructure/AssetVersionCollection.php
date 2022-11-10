<?php

declare(strict_types=1);

namespace Phant\AssetsVersions\DataStructure;

use Phant\AssetsVersions\DataStructure\AssetVersion;

final class AssetVersionCollection
{
    private array $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    public function add(AssetVersion $assetVersion): void
    {
        $this->collection[ $assetVersion->assetPath ] = $assetVersion;
    }

    public function findFromAssetPath(string $assetPath): ?AssetVersion
    {
        return $this->collection[ $assetPath ] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->collection);
    }
}
