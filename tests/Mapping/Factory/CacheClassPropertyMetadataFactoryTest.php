<?php


namespace IIIRxs\ImageUploadBundle\Tests\Mapping\Factory;


use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Mapping\Factory\ClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImageContainer;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

class CacheClassPropertyMetadataFactoryTest extends TestCase
{

    public function testHasMetadataFor()
    {
        $config = [
            TestImageContainer::class => [
                'fields' => [
                    'images' => [
                        'class' => AbstractImage::class,
                        'entry_type' => ImageType::class,
                        'directories' => [
                            'optimized' => 'test',
                            'thumbnails' => 'test'
                        ]
                    ]
                ]
            ]
        ];

        $documentManager = $this->createMock(DocumentManager::class);
        $factory = new ClassPropertyMetadataFactory($documentManager, $config, '');

        $cacheProvider = $this->createMock(CacheItemPoolInterface::class);

        $cacheProvider
            ->expects($this->once())
            ->method('hasItem')
            ->willReturn(false);

        $cacheFactory = new CacheClassPropertyMetadataFactory($factory, $cacheProvider);

        $this->assertTrue($cacheFactory->hasMetadataFor(TestImageContainer::class, 'images'));
    }

}