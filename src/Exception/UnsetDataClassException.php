<?php


namespace IIIRxs\ImageUploadBundle\Exception;


use IIIRxs\ExceptionHandlerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class UnsetDataClassException extends \Exception implements ExceptionInterface
{

    public function getStatusCode()
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getPayload()
    {
        return ['errors' => 'Data class for image type not set!'];
    }
}