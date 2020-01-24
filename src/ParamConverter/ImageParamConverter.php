<?php


namespace IIIRxs\ImageUploadBundle\ParamConverter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ImageParamConverter implements ParamConverterInterface
{

    /** @var DocumentManager */
    private $documentManager;

    /** @var array */
    private $mappings;

    public function __construct(DocumentManager $documentManager, array $mappings)
    {
        $this->documentManager = $documentManager;
        $this->mappings = $mappings;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $requestClassName = $request->attributes->get('className');
        $className = $this->kebapToCamelCaseParameter($requestClassName);

        $matchedClass = $this->getMatchedClass($className);

        if (is_null($matchedClass)) {
            return false;
        }

        $id = $request->attributes->get('id');
        $repository = $this->documentManager->getRepository($matchedClass);
        $object = $repository->find($id);

        if (!is_object($object)) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $object);
        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        $options = $configuration->getOptions();
        return isset($options['case']) && $options['case'] === 'images';
    }

    private function getMatchedClass(string $className): ?string
    {
        $classNames = !empty($this->mappings)
            ? array_keys($this->mappings)
            : $this->documentManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

        $pattern = '/.' . $className . '$/';

        $matchedClasses = array_values(preg_grep($pattern, $classNames));
        return empty($matchedClasses) ? null : $matchedClasses[0];
    }

    private function kebapToCamelCaseParameter(string $parameter)
    {
        return str_replace('-', '', ucwords($parameter, '-'));
    }

}