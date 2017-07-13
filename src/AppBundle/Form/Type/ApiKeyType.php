<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class ApiKeyType extends TextType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'apikey';
    }
}
