<?php
declare(strict_types=1);

namespace Elephox\Cache\APCu;

use APCuIterator;
use DateTime;
use Elephox\Cache\AbstractCache;
use Elephox\Cache\Contract\CacheConfiguration;
use Elephox\Cache\InvalidKeyTypeException;
use Elephox\Cache\InvalidTtlException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use const APC_ITER_KEY;

class APCuCache extends AbstractCache
{
	/** @var array<string, APCuCacheItem> */
	protected array $deferred = [];

	public function __construct(
		private readonly APCuCacheConfiguration $configuration,
	) {
		assert(apcu_enabled(), 'APCu is not enabled');
	}

	public function getConfiguration(): CacheConfiguration
	{
		return $this->configuration;
	}

	public function getItem(string $key): CacheItemInterface
	{
		$ttl = $this->calculateExpiresAt(new DateTime());
		return apcu_entry($key, static fn () => new APCuCacheItem($key, null, true, $ttl), $ttl);
	}

	public function hasItem(string $key): bool
	{
		return apcu_exists($key);
	}

	public function clear(): bool
	{
		return apcu_clear_cache();
	}

	public function deleteItem(string $key): bool
	{
		return apcu_delete($key);
	}

	/**
	 * @throws InvalidTtlException
	 */
	public function save(CacheItemInterface $item): bool
	{
		return apcu_store($item->getKey(), $item, $this->calculateExpiresAt(new DateTime()));
	}

	public function saveDeferred(CacheItemInterface $item): bool
	{
		$this->deferred[] = $item;

		return true;
	}

	/**
	 * @throws InvalidTtlException
	 */
	public function commit(): bool
	{
		while ($item = array_pop($this->deferred)) {
			$this->save($item);
		}

		return true;
	}
}
