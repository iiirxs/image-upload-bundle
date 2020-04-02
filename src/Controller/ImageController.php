<?php

namespace IIIRxs\ImageUploadBundle\Controller;

use Doctrine\ODM\MongoDB\MongoDBException;
use Exception;
use IIIRxs\ExceptionHandlerBundle\Exception\Api\UnreachableCodeException;
use IIIRxs\ExceptionHandlerBundle\Exception\Api\ValidationException;
use IIIRxs\ImageUploadBundle\Event\ImageDetailsPostEvent;
use IIIRxs\ImageUploadBundle\Exception\UnsetDataClassException;
use IIIRxs\ImageUploadBundle\Form\ImageFormService;
use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ImageController extends AbstractController
{

    /**
     * @var NormalizerInterface
     */
    private $errorNormalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->errorNormalizer = $normalizer;
    }

    /**
     * @param $object
     * @param $fieldName
     * @param Request $request
     * @param EventDispatcherInterface $dispatcher
     * @param ImageFormService $imageFormService
     * @param DocumentManager $documentManager
     * @return JsonResponse
     * @throws MongoDBException
     * @throws Exception
     * @throws ExceptionInterface
     * @ParamConverter("object", options={ "case": "images" }, converter="iiirxs_image_upload.param_converter")
     */
    public function uploadImages(
        $object,
        $fieldName,
        Request $request,
        EventDispatcherInterface $dispatcher,
        ImageFormService $imageFormService,
        DocumentManager $documentManager
    )
    {
        $fieldName = lcfirst(str_replace('-', '', ucwords($fieldName, '-')));
        $form = $imageFormService->createForm($object, $fieldName);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $dispatcher->dispatch(new ImagesUploadEvent($accessor->getValue($object, $fieldName), $object, $fieldName));
            $documentManager->flush();
            return $this->json([], Response::HTTP_NO_CONTENT);
        } else if ($form->isSubmitted()) {
            throw new ValidationException($this->errorNormalizer->normalize($form));
        }

        throw new UnreachableCodeException();
    }

    /**
     * @param $object
     * @param string $fieldName
     * @param Request $request
     * @param EventDispatcherInterface $dispatcher
     * @param ImageFormService $imageFormService
     * @param DocumentManager $documentManager
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws MongoDBException
     * @throws ValidationException
     * @ParamConverter(name="object", options={ "case": "images" }, converter="iiirxs_image_upload.param_converter")
     */
    public function postImageDetails(
        $object,
        string $fieldName,
        Request $request,
        EventDispatcherInterface $dispatcher,
        ImageFormService $imageFormService,
        DocumentManager $documentManager
    )
    {
        $fieldName = lcfirst(str_replace('-', '', ucwords($fieldName, '-')));
        $form = $imageFormService->createForm($object, $fieldName);

        $form->submit(json_decode($request->getContent(),true));

        if (!$form->isValid()) {
            throw new ValidationException($this->errorNormalizer->normalize($form));
        }

        $dispatcher->dispatch(new ImageDetailsPostEvent($object));

        $documentManager->flush();
        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}