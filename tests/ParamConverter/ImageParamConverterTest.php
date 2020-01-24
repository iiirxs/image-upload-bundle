<?php


namespace IIIRxs\ImageUploadBundle\Tests\ParamConverter;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use IIIRxs\ImageUploadBundle\ParamConverter\ImageParamConverter;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ImageParamConverterTest extends TestCase
{

    public function testSupports()
    {
        $imageParamConverter = new ImageParamConverter($this->createMock(DocumentManager::class), []);

        $paramConverter = new ParamConverter([ 'options' => ['case' => 'images'] ]);
        $this->assertTrue($imageParamConverter->supports($paramConverter));

        $paramConverter = new ParamConverter([]);
        $this->assertFalse($imageParamConverter->supports($paramConverter));
    }

    public function testApply()
    {
        $mappings = [ 'App\TestImage' => [] ];
        $documentManager = $this->createMock(DocumentManager::class);
        $repository = $this->createMock(DocumentRepository::class);

        $dummyObject = new \stdClass();
        $repository
            ->expects($this->once())
            ->method('find')
            ->willReturn($dummyObject)
        ;

        $documentManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);
        ;
        $imageParamConverter = new ImageParamConverter($documentManager, $mappings);

        $request = $this->createMock(Request::class);
        $request->attributes = new ParameterBag(['className' => 'test-image']);

        $paramConverter = new ParamConverter(['name' => 'object']);

        $this->assertTrue($imageParamConverter->apply($request, $paramConverter));
        $this->assertEquals($dummyObject, $request->attributes->get('object'));
    }

}