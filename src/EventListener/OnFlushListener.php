<?php


namespace IIIRxs\ImageUploadBundle\EventListener;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
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

        $mappings = array_reduce($imagesToBeDeleted, function ($curry, ImageInterface $image) use ($uow) {
            list($mapping, $parent, $propertyPath) = $uow->getParentAssociation($image);
            $curry[get_class($parent)][$mapping['fieldName']][] = $image;
            return $curry;
        }, []);

        foreach ($mappings as $class => $mapping) {
            foreach ($mapping as $field => $images) {
               $this->eventDispatcher->dispatch(new ImagesDeleteEvent(new ArrayCollection($images), $class, $field));
            }
        }

//        foreach ($uow->getScheduledCollectionDeletions() as $col) {
//            dump($col);
//        }
//
//        foreach ($uow->getScheduledCollectionUpdates() as $col) {
//            dump($col);
//        }
    }

}