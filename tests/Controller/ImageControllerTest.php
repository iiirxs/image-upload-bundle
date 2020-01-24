<?php


namespace IIIRxs\ImageUploadBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ExceptionHandlerBundle\Exception\Api\UnreachableCodeException;
use IIIRxs\ExceptionHandlerBundle\Exception\Api\ValidationException;
use IIIRxs\ImageUploadBundle\Tests\Kernel\IIIRxsImageUploadTestingKernel;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImageContainer;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $browser->catchExceptions(false);
        $this->doHttpRequest($browser, self::UPLOAD_PATH, $form);
        $this->assertEquals(204, $browser->getResponse()->getStatusCode());
    }

    public function testUploadImagesWithInvalidForm()
    {
        $this->expectException(ValidationException::class);
        $form['image_collection']['images'][]['image'] = ['invalid' => '1'];
        $form['image_collection'] = 11;

        $browser = $this->bootTest();
        $browser->catchExceptions(false);
        $this->doHttpRequest($browser, self::UPLOAD_PATH, $form);
    }

    public function testUploadImagesWithoutForm()
    {
        $this->expectException(UnreachableCodeException::class);
        $browser = $this->bootTest();
        $browser->catchExceptions(false);
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
        $browser->catchExceptions(false);
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
        $config = [
            'default_image_upload_dir' => [
                'optimized' => '%kernel.project_dir%/tests/files/optimized',
                'thumbnails' => '%kernel.project_dir%/tests/files/thumbnails',
            ]
        ];
        $kernel = new IIIRxsImageUploadTestingKernel('test', true, $config);
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