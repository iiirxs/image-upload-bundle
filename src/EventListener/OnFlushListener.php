<?php


namespace IIIRxs\ImageUploadBundle\EventListener;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\UnitOfWork;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;
use IIIRxs\ImageUploadBundle\Event\ImagesDeleteEvent;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageCollectionException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OnFlushListener
{

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     * @throws InvalidImageCollectionException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $dm = $eventArgs->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $imagesToBeDeleted = array_filter($uow->getScheduledDocumentDeletions(), function ($document) {
            return $document instanceof ImageInterface;
        });

        $mappings = $this->getDocumentMappings($imagesToBeDeleted, $uow);

        foreach ($mappings as $class => $mapping) {
            foreach ($mapping as $field => $images) {
               $this->eventDispatcher->dispatch(new ImagesDeleteEvent(new ArrayCollection($images), $class, $field));
            }
        }
    }

    private function getDocumentMappings(array $imagesToBeDeleted, UnitOfWork $uow): array
    {
        return array_reduce($imagesToBeDeleted, function ($carry, ImageInterface $image) use ($uow) {
            list($mapping, $parent, $propertyPath) = $uow->getParentAssociation($image);

            if (is_null($parent)) {
                return [];
            }

            $carry[get_class($parent)][$mapping['fieldName']][] = $image;
            return $carry;
        }, []);
    }

}