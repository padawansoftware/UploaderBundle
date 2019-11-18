# UploaderBundle

UploaderBundle is a wrapper for [VichUploaderBundle](https://github.com/dustin10/VichUploaderBundle).

This bundle adds facilities for managing assets upload by providing a base Asset entity class and a set of tools for managing it.

## Configure VichUploaderBundle

As this bundle is just a wrapper for VichUploaderBundle, you must [configure](https://github.com/dustin10/VichUploaderBundle/blob/master/Resources/doc/index.md) it first.

It is required that the configuration has a mapping named `asset`, that will be used for our `Asset` entity:

```yml
# config/packages/vich_uploader.yaml or app/config/config.yml
vich_uploader:
    db_driver: orm

    mappings:
        asset: # it is important that one mapping has name asset
            uri_prefix: /media
            upload_destination: '%kernel.project_dir%/public/media'
```

## Entity as standalone class

If you're planning to use your Entity class as standalone class and not associated to another entity, isenought to create a class that extends from bundle Asset


```php
use Doctrine\ORM\Mapping as ORM;
use PSUploaderBundle\Entity\Asset as BaseAsset;

/**
 * @ORM\Entity
 */
class Asset extends BaseAsset {
}
```

That's all you need. Now when you create/update your database schema, a new class will be created:
```
+------------------+--------------+------+-----+---------+----------------+
| Field            | Type         | Null | Key | Default | Extra          |
+------------------+--------------+------+-----+---------+----------------+
| asset_id         | int(11)      | NO   | PRI | NULL    | auto_increment |
| asset_name       | varchar(255) | NO   |     | NULL    |                |
| asset_updated_at | date         | NO   |     | NULL    |                |
+------------------+--------------+------+-----+---------+----------------+
```

## Use Asset with associated entity

It is common that `Asset` entity is related to another entities. For example, a Post in a blog may have a related image as header, or a Tutorial can have a file as download. Thus, this bundle has facilities to manage the association.

For associate Asset with another entity, add Doctrine ORM association in the entity class.

```php
use Doctrine\ORM\Mapping as ORM;

class Post
{
    /**
     * @var Asset
     *
     * @ORM\OneToOne(targetEntity="Asset")
     * @ORM\JoinColumn(name="post_image", referencedColumnName="asset_id")
     */
    protected $image;
}
```

Now you can fetch our assets from the associated entity.

When associating entities with Asset it may happend that you only associate with one entity or you can associate with multiple entities.

### Association with one entity

If you're association Asset with only one Entity, you can also add association in the Asset side:

```php
use Doctrine\ORM\Mapping as ORM;
use PSUploaderBundle\Entity\Asset as BaseAsset;

/**
 * @ORM\Entity
 */
class Asset extends BaseAsset {

    /**
     * @var mixed
     *
     * The post this asset it attached to
     *
     * @ORM\OneToOne(targetEntity="Post", mappedBy="asset")
     */
    protected $post;
}
```

You also need to make a change in the associated entity:

```php
use Doctrine\ORM\Mapping as ORM;

class Post
{
    /**
     * @var Asset
     *
     * @ORM\OneToOne(targetEntity="Asset", inversedBy="post")
     * @ORM\JoinColumn(name="post_image", referencedColumnName="asset_id")
     */
    protected $image;
}
```


This way, you can also fetch associated entity from the `Asset` entity.


### Association with more than one entity

As we've comment before, if you're planning to associate Asset entity with more than one entity, you can't add association in the Asset entity as you would have to specify the `targetEntity`. This does not meen you cant fetch associated entity from the `Asset` entity. Fortunately this bundle has the solution:

First, your `Asset` class must implement `PSUploaderBundle\Library\Interfaces\EntityAssetInterface` and the two methods defined:

```php
use Doctrine\ORM\Mapping as ORM;
use PSUploaderBundle\Entity\Asset as BaseAsset;
use PSUploaderBundle\Library\Interfaces\EntityAssetInterface;

/**
 * @ORM\Entity
 */
class Asset extends BaseAsset implements EntityAssetInterface
{
    /**
     * @var mixed
     *
     * The entity this asset it attached to
     */
    protected $entity;


    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $entity
     *
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }
}
```

Then in your associated entities, you have to annotate with field holds the `Asset`:

```php
use Doctrine\ORM\Mapping as ORM;
use PSUploaderBundle\Library\Annotation\Asset as AssetAnnotation;

/**
 * @AssetAnnotation("image")
 **/
class Post
{
    /**
     * @var Asset
     *
     * @ORM\OneToOne(targetEntity="Asset")
     * @ORM\JoinColumn(name="post_image", referencedColumnName="asset_id")
     */
    protected $image;
}
```

Finally, you've to register `PSUploaderBundle\EventListener\InjectEntityListener` as a service:

```yml
    PSUploaderBundle\EventListener\InjectEntityListener:
        tags:
            - { name: doctrine.event_subscriber }
```

Wich this `InjectEntityListener` does is listen when an entity is fetched from database and, if that entity has an associated `Asset`, injects the entity into the `Asset`.


### Add validation for image Asset

You may want to add an `Image` validation constraint for the `Asset`. Constraints must be set on the final field, so trying to add them in the `Asset` field of the associated entity won't work as it is not the real file:

```php
use Doctrine\ORM\Mapping as ORM;
use PSUploaderBundle\Library\Annotation\Asset as AssetAnnotation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssetAnnotation("image")
 **/
class Post
{
    /**
     * @var Asset
     *
     * @ORM\OneToOne(targetEntity="Asset")
     * @ORM\JoinColumn(name="post_image", referencedColumnName="asset_id")
     *
     * Assert image and 23/9 aspect ratio
     * @Assert\Image (
     *  minRatio = 2.55,
     *  maxRatio = 2.56
     * )
     */
    protected $image;
}
```

Fortunately, this bundle comes with a custom `Image` constraint for solving that:

```php
use Doctrine\ORM\Mapping as ORM;
use PSUploaderBundle\Library\Annotation\Asset as AssetAnnotation;
use PSUploaderBundle\Library\Validation\ImageAsset as ImageAssetConstraint;

/**
 * @AssetAnnotation("image")
 **/
class Post
{
    /**
     * @var Asset
     *
     * @ORM\OneToOne(targetEntity="Asset")
     * @ORM\JoinColumn(name="post_image", referencedColumnName="asset_id")
     *
     * Assert image and 23/9 aspect ratio
     * @ImageAssetConstraint (
     *  minRatio = 2.55,
     *  maxRatio = 2.56
     * )
     */
    protected $image;
}
```
