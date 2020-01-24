<?php


namespace IIIRxs\ImageUploadBundle\Tests\Controller;

use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ExceptionHandlerBundle\Exception\Api\UnreachableCodeException;
use IIIRxs\ExceptionHandlerBundle\Exception\Api\ValidationException;
use IIIRxs\ImageUploadBundle\IIIRxsImageUploadBundle;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImageContainer;
use IIIRxs\ValidationErrorNormalizerBundle\IIIRxsValidationErrorNormalizerBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ImageControllerTest extends WebTestCase
{

    const UPLOAD_PATH = '/test-image-container/images/upload/';
    const POST_DETAILS_PATH = '/test-image-container/images/details/';

    private $objectsToClear;

    /** @var DocumentManager */
    private $documentManager;

    public function tearDown(): void
    {
        if ($this->documentManager instanceof DocumentManager) {
            foreach ($this->objectsToClear as $object) {
                $this->documentManager->remove($object);
            }

            $this->documentManager->flush();
        }
    }

    public function testUploadImages()
    {
        $form['image_collection']['images'] = [];
        $browser = $this->bootTest();
        $this->doHttpRequest($browser, self::UPLOAD_PATH, $form);

        $this->assertEquals(204, $browser->getResponse()->getStatusCode());
    }

    public function testUploadImagesWithInvalidForm()
    {
        $this->expectException(ValidationException::class);
        $form['image_collection']['images'][]['image'] = ['invalid' => '1'];
        $form['image_collection'] = 11;

        $browser = $this->bootTest();
        $this->doHttpRequest($browser, self::UPLOAD_PATH, $form);
    }

    public function testUploadImagesWithoutForm()
    {
        $this->expectException(UnreachableCodeException::class);
        $browser = $this->bootTest();
        $this->doHttpRequest($browser, self::UPLOAD_PATH);
    }

    public function testPostDetails()
    {
        $data['images'] = [ ['rank' => 3] ];

        $browser = $this->bootTest();

        $testImageContainer = $this->doHttpRequest($browser, self::POST_DETAILS_PATH, [], $data);

        $this->assertEquals(204, $browser->getResponse()->getStatusCode());

        $this->documentManager->refresh($testImageContainer);
        $this->assertCount(1, $testImageContainer->getImages()->toArray());
        $this->assertEquals(3, $testImageContainer->getImages()->first()->getRank());
    }

    public function testPostDetailsWithInvalidData()
    {
        $this->expectException(ValidationException::class);

        $data['images'] = [ ['rank' => [] ] ];

        $browser = $this->bootTest();
        $this->doHttpRequest($browser, self::POST_DETAILS_PATH, [], $data);
    }

    /**
     * @param KernelBrowser $browser
     * @param $baseUrl
     * @param array $form
     * @param array $data
     * @return TestImageContainer
     */
    private function doHttpRequest(KernelBrowser $browser, $baseUrl, $form = [], $data = null): TestImageContainer
    {
        $testImageContainer = $this->storeTestImageContainer();
        $this->objectsToClear[] = $testImageContainer;

        $url = $baseUrl . $testImageContainer->getId();
        $data = !empty($data) ? json_encode($data) : null;
        $browser->request('POST', $url, $form, [], [], $data);
        return $testImageContainer;
    }

    /**
     * @return KernelBrowser
     */
    private function bootTest(): KernelBrowser
    {
        $kernel = new IIIRxsImageUploadTestingKernel();
        $kernel->boot();
        $this->documentManager = $kernel->getContainer()->get('doctrine_mongodb.odm.document_manager');

        return new KernelBrowser($kernel);
    }

    private function storeTestImageContainer()
    {
        $testImageContainer = new TestImageContainer();
        $this->documentManager->persist($testImageContainer);
        $this->documentManager->flush();
        return $testImageContainer;
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