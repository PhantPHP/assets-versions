<?php

declare(strict_types=1);

namespace Phant\AssetsVersions\DataStructure;

final class AssetVersion
{
    public const TYPE_VERSION = 'ver';
    public const TYPE_MODIFICATION_TIME = 'lmod';

    public function __construct(
        public readonly string $assetPath,
        public readonly string $type,
        public readonly int $value
    ) {
    }

    public function __toString(): string
    {
        return $this->assetPath . '?' . http_build_query([
            $this->type => $this->value
        ]);
    }
}
