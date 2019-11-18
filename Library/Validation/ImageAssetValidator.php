<?php

namespace PSUploaderBundle\Library\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ImageValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ImageAssetValidator extends ImageValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ImageAsset) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ImageAsset');
        }

        if (null === $value || '' === $value) {
            return;
        }

        parent::validate($value->getFile(), $constraint);
    }
}
