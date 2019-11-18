<?php
namespace PSUploaderBundle\Library\Interfaces;

/**
 * Interface for assets associated to another entities
 */
interface EntityAssetInterface
{
    public function setEntity($entity);
    public function getEntity();
}
