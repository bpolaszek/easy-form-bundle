<?php

namespace BenTools\EasyFormBundle\Form;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DeletionFormBuilderFactory
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * DeletionFormBuilderFactory constructor.
     * @param PropertyAccessor $propertyAccessor
     * @param ManagerRegistry  $managerRegistry
     * @param FormFactory      $formFactory
     */
    public function __construct(
        PropertyAccessor $propertyAccessor,
        ManagerRegistry $managerRegistry,
        FormFactory $formFactory
    ) {
    
        $this->propertyAccessor = $propertyAccessor;
        $this->managerRegistry = $managerRegistry;
        $this->formFactory = $formFactory;
    }

    /**
     * @param             $entity
     * @param array       $options
     * @param string|null $url
     * @return FormBuilderInterface
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function createFormBuilderFromEntity($entity, array $options = [], string $url = null): FormBuilderInterface
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(sprintf("Expected Doctrine entity, got %s", gettype($entity)));
        }
        $class = get_class($entity);
        $manager = $this->managerRegistry->getManagerForClass($class);
        $metadata = $manager->getClassMetadata($class);
        $identifier = $metadata->getIdentifier();

        if (!isset($identifier[0]) || 1 !== count($identifier)) {
            throw new \RuntimeException(sprintf("Unable to parse identifier for %s", $class));
        }

        $idProperty = $identifier[0];
        if (!$this->propertyAccessor->isReadable($entity, $idProperty)) {
            throw new \RuntimeException(sprintf("Unable to read identifier for %s", $class));
        }

        $id = $this->propertyAccessor->getValue($entity, $idProperty);
        $fb = $this->formFactory->createBuilder(FormType::class);
        $fb->setMethod('DELETE');
        $fb->setAction($url);
        $fb->add('class', HiddenType::class, [
            'data' => $class,
        ]);
        $fb->add('id', HiddenType::class, [
            'data' => $id,
        ]);
        $fb->add('submit', SubmitType::class, array_replace(['label' => 'Delete'], $options));
        return $fb;
    }

    /**
     * @param string $class
     * @param        $id
     * @return FormBuilderInterface
     * @throws \RuntimeException
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function createFormBuilderFromClassAndId(string $class, $id): FormBuilderInterface
    {
        $manager = $this->managerRegistry->getManagerForClass($class);
        $metadata = $manager->getClassMetadata($class);
        $identifier = $metadata->getIdentifier();

        if (!isset($identifier[0]) || 1 !== count($identifier)) {
            throw new \RuntimeException(sprintf("Unable to parse identifier for %s", $class));
        }

        $fb = $this->formFactory->createBuilder(FormType::class);
        $fb->setMethod('DELETE');
        $fb->add('class', HiddenType::class, [
            'data' => $class,
        ]);
        $fb->add('id', HiddenType::class, [
            'data' => $id,
        ]);
        $fb->add('submit', SubmitType::class);
        return $fb;
    }
}
