<?php


namespace IIIRxs\ImageUploadBundle\Tests;


use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use IIIRxs\ImageUploadBundle\Form\ImageFormService;
use IIIRxs\ImageUploadBundle\IIIRxsImageUploadBundle;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImage;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use IIIRxs\ValidationErrorNormalizerBundle\IIIRxsValidationErrorNormalizerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class IntegrationTest extends TestCase
{

    public function testServiceWiring()
    {
        $kernel = new IIIRxsImageUploadTestingKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $imageFormService = $container->get('iiirxs_image_upload.form.image_form_service');
        $chainUploader = $container->get('iiirxs_image_upload.uploader.chain_uploader');
        $this->assertInstanceOf(ImageFormService::class, $imageFormService);
        $this->assertInstanceOf(ChainUploader::class, $chainUploader);
    }

    public function testServiceWiringWithConfiguration()
    {

        $mappings = [
            TestImage::class => []
        ];

        $config = [
            'max_thumbnail_dimension' => 600,
            'mappings' => $mappings
        ];

        $kernel = new IIIRxsImageUploadTestingKernel($config);
        $kernel->boot();
        $container = $kernel->getContainer();

        $imageFormService = $container->get('iiirxs_image_upload.form.image_form_service');
        $this->assertInstanceOf(ImageFormService::class, $imageFormService);

    }

}

class IIIRxsImageUploadTestingKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @var array
     */
    private $imageUploadConfig;

    public function __construct(array $imageUploadConfig = [])
    {
        $this->imageUploadConfig = $imageUploadConfig;
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new IIIRxsImageUploadBundle(),
            new IIIRxsValidationErrorNormalizerBundle(),
            new FrameworkBundle(),
            new DoctrineMongoDBBundle()
        ];
    }

    /**
     * @inheritDoc
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    /**
     * @inheritDoc
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->loadFromExtension('framework', [
            'secret' => 'F00',
        ]);

        $container->loadFromExtension('doctrine_mongodb', [
            'connections' => [
                'default' => [
                    'server' => 'mongodb://127.0.0.1:8000'
                ]
            ],
            'document_managers' => [
                'default' => [
                    'auto_mapping' => true
                ]
            ],
        ]);

        $container->loadFromExtension('iiirxs_image_upload', $this->imageUploadConfig);
    }

    public function getCacheDir()
    {
        return __DIR__.'/cache/'.spl_object_hash($this);
    }


}