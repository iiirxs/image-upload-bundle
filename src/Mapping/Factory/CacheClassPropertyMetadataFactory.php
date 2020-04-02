<?php


namespace IIIRxs\ImageUploadBundle\Mapping\Factory;


use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadataInterface;
use Psr\Cache\CacheItemPoolInterface;


class CacheClassPropertyMetadataFactory implements ClassPropertyMetadataFactoryInterface
{
    /**
     * @var ClassPropertyMetadataFactoryInterface
     */
    private $decorated;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    public function __construct(ClassPropertyMetadataFactoryInterface $decorated, CacheItemPoolInterface $cacheItemPool)
    {
        $this->decorated = $decorated;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($class, $property): ClassPropertyMetadataInterface
    {
        $class = \is_string($class) ? $class : get_class($class);

        $item = $this->cacheItemPool->getItem($this->getKey($class, $property));
        if ($item->isHit()) {
            return $item->get();
        }

        $metadata = $this->decorated->getMetadataFor($class, $property);
        $this->cacheItemPool->save($item->set($metadata));

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($class, $property): bool
    {
        $class = \is_string($class) ? $class : get_class($class);

        return $this->cacheItemPool->hasItem($this->getKey($class, $property))
            || $this->decorated->hasMetadataFor($class, $property);
    }

    protected function getKey($class, $property)
    {
        // Key cannot contain backslashes according to PSR-6
        return strtr($class, '\\', '_') . '~' . $property;
    }
}