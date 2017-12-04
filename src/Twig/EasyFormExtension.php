<?php

namespace BenTools\EasyFormBundle\Twig;

use BenTools\EasyFormBundle\Form\DeletionFormBuilderFactory;
use BenTools\EasyFormBundle\Form\ToggleFormBuilderFactory;
use Twig_Extension;
use Twig_SimpleFunction;

class EasyFormExtension extends Twig_Extension
{
    /**
     * @var DeletionFormBuilderFactory
     */
    private $deletionFormBuilderFactory;
    /**
     * @var ToggleFormBuilderFactory
     */
    private $toggleFormBuilderFactory;

    /**
     * @inheritDoc
     */
    public function __construct(
        DeletionFormBuilderFactory $deletionFormBuilderFactory,
        ToggleFormBuilderFactory $toggleFormBuilderFactory
    ) {
    
        $this->deletionFormBuilderFactory = $deletionFormBuilderFactory;
        $this->toggleFormBuilderFactory = $toggleFormBuilderFactory;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('createDeletionForm', function ($entity, array $options = [], string $url = null) {
                return $this->deletionFormBuilderFactory->createFormBuilderFromEntity($entity, $options, $url)->getForm()->createView();
            }),
            new Twig_SimpleFunction('createToggleForm', function ($entity, array $options = [], string $url = null) {
                return $this->toggleFormBuilderFactory->createFormBuilderFromEntity($entity, $options, $url)->getForm()->createView();
            }),
        ];
    }
}
