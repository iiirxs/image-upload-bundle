<?php

namespace IIIRxs\ImageUploadBundle\Exception;

use IIIRxs\ExceptionHandlerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidMappedClassException extends \Exception implements ExceptionInterface
{

    private $errorMessage;

    public function __construct($errorMessage)
    {

        $this->errorMessage = $errorMessage;
        parent::__construct();
    }

    public function getStatusCode()
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getPayload()
    {
        return ['errors' => $this->errorMessage];
    }
}