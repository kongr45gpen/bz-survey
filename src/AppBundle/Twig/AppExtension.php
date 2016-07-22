<?php

namespace AppBundle\Twig;

use Symfony\Component\PropertyAccess\PropertyAccess;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('map', function($array, $attribute) {
                $accessor = PropertyAccess::createPropertyAccessor();

                foreach($array as $element) {
                    yield $accessor->getValue($element, $attribute);
                }
            }),
        ];
    }

    public function getName()
    {
        return 'app_extension';
    }
}
