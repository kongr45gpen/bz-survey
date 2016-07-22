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
            new \Twig_SimpleFilter('natsort', function($array) {
                if (!is_array($array)) {
                    $array = iterator_to_array($array);
                }

                natcasesort($array);

                return $array;
            }),
        ];
    }

    public function getName()
    {
        return 'app_extension';
    }
}
