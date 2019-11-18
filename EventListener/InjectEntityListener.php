<?php
namespace PSUploaderBundle\EventListener;

use \ReflectionClass;
use PSUploaderBundle\Library\Interfaces\EntityAssetInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Inject entity into Assets
 */
class InjectEntityListener implements EventSubscriber
{
    protected $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($this->hasAsset($object)) {
            $this->populateAsset($object);
        }
    }

    /**
     * Check whether this entity has an assest field
     */
    protected function hasAsset($object): bool
    {
        $reflectionClass = new ReflectionClass($object);

        return null !== $this->reader->getClassAnnotation($reflectionClass, 'PSUploaderBundle\Library\Annotation\Asset');
    }

    /**
     * Inject the entity into the asset and populate fields
     */
    protected function populateAsset($object)
    {
        $reflectionClass = new ReflectionClass($object);

        if ($assetField = $this->getAssetField($reflectionClass)) {
            $methodName = 'get'. ucfirst($assetField);

            if ($reflectionClass->hasMethod($methodName)) {
                $reflectionMethod = $reflectionClass->getMethod($methodName);
                if (($asset = $reflectionMethod->invoke($object)) instanceof EntityAssetInterface) {
                    // Populate asset with entity
                    $this->injectEntity($asset, $object);
                }
            }
        }
    }

    /**
     * Return the field that holds the asset
     */
    protected function getAssetField(ReflectionClass $reflectionClass)
    {
        if ($annotation = $this->reader->getClassAnnotation($reflectionClass, 'PSUploaderBundle\Library\Annotation\Asset')) {
            return $annotation->value;
        }

        return null;
    }

    /**
     * Inject the entity into the asset
     */
    protected function injectEntity($asset, $entity)
    {
        $asset->setEntity($entity);
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postLoad
        ];
    }
}
