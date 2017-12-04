<?php

namespace BenTools\EasyFormBundle\Action;

use BenTools\EasyFormBundle\Form\DeletionFormBuilderFactory;
use BenTools\EasyFormBundle\Form\FormException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DeleteEntityAction
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var DeletionFormBuilderFactory
     */
    private $formBuilderFactory;

    /**
     * DeleteEntityAction constructor.
     * @param ManagerRegistry            $managerRegistry
     * @param DeletionFormBuilderFactory $formBuilderFactory
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        DeletionFormBuilderFactory $formBuilderFactory
    ) {
    
        $this->managerRegistry = $managerRegistry;
        $this->formBuilderFactory = $formBuilderFactory;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request)
    {
        $form = $request->request->get('form');
        if (!isset($form['class']) || !isset($form['id'])) {
            throw new BadRequestHttpException();
        }
        $class = $form['class'];
        $id = $form['id'];
        $manager = $this->managerRegistry->getManagerForClass($class);
        $entity = $manager->find($class, $id);

        if (null === $entity) {
            throw new \RuntimeException(sprintf("Entity %s not found for class %s", $id, $class));
        }

        $fb = $this->formBuilderFactory->createFormBuilderFromClassAndId($class, $id);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $manager->remove($entity);
            $manager->flush();
        } else {
            throw new FormException($form);
        }
        return $entity;
    }
}
