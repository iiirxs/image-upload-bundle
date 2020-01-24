<?php


namespace IIIRxs\ImageUploadBundle\Tests\Kernel;


use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use IIIRxs\ImageUploadBundle\IIIRxsImageUploadBundle;
use IIIRxs\ValidationErrorNormalizerBundle\IIIRxsValidationErrorNormalizerBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class IIIRxsImageUploadTestingKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var array
     */
    private $imageUploadConfig;

    public function __construct($environment = 'test', $debug = true, array $imageUploadConfig = [])
    {
        $this->imageUploadConfig = $imageUploadConfig;
        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        return [
            new IIIRxsImageUploadBundle(),
            new IIIRxsValidationErrorNormalizerBundle(),
            new FrameworkBundle(),
            new DoctrineMongoDBBundle(),
            new SensioFrameworkExtraBundle(),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(__DIR__.'/../../src/Resources/config/routes.xml', '/');
    }

    /**
     * @inheritDoc
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('framework', [
            'secret' => 'F00',
        ]);

        $container->loadFromExtension('sensio_framework_extra', [
            'router' => ['annotations' => false],
        ]);

        $container->loadFromExtension('doctrine_mongodb', [
            'connections' => [
                'default' => [
                    'server' => 'mongodb://127.0.0.1:27017'
                ]
            ],
            'document_managers' => [
                'default' => [
                    'database' => 'image_upload_bundle_testing',
                    'mappings' => [
                        'Test' => [
                            'is_bundle' => false,
                            'type' => 'xml',
                            'dir' => '%kernel.project_dir%/tests/Util/',
                            'prefix' => 'IIIRxs\ImageUploadBundle\Tests\Util'
//                            'mapping' => true,
                        ]
                    ]
                ]
            ],
        ]);

        $container->loadFromExtension('iiirxs_image_upload', $this->imageUploadConfig);
    }

    public function getCacheDir()
    {
        return __DIR__.'/../cache/'.spl_object_hash($this);
    }


}