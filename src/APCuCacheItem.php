<?php
declare(strict_types=1);

namespace Elephox\Cache\APCu;

use Elephox\Cache\ImmutableCacheItem;
use JetBrains\PhpStorm\Immutable;

/**
 * @psalm-consistent-constructor
 */
#[Immutable]
class APCuCacheItem extends ImmutableCacheItem
{
}
