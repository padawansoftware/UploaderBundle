<?php
namespace PSUploaderBundle\Entity;

use \DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
class Asset
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="asset_id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="asset", fileNameProperty="name")
     */
    protected $file;

    /**
     * @var string
     *
     * @ORM\Column(name="asset_name", type="string")
     */
    protected $name;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="asset_updated_at", type="date")
     */
    protected $updatedAt;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @return self
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        if ($file) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }
}
