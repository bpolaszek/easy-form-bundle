<?php

namespace BenTools\EasyFormBundle\Action;

use BenTools\EasyFormBundle\Form\FormException;
use BenTools\EasyFormBundle\Form\ToggleFormBuilderFactory;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Model\ToggleableInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ToggleEntityAction
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var ToggleFormBuilderFactory
     */
    private $formBuilderFactory;

    /**
     * ToggleEntityAction constructor.
     * @param ManagerRegistry          $managerRegistry
     * @param ToggleFormBuilderFactory $formBuilderFactory
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ToggleFormBuilderFactory $formBuilderFactory
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

        if (!$entity instanceof ToggleableInterface) {
            throw new UnexpectedTypeException($entity, ToggleableInterface::class);
        }

        $fb = $this->formBuilderFactory->createFormBuilderFromClassAndId($class, $id);
        $form = $fb->getForm();

        $action = $form->get('action')->getData();
        if (in_array($action, ['toggle', 'enable', 'disable'])) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                switch ($action) {
                    case 'enable':
                        $entity->enable();
                        break;
                    case 'disable':
                        $entity->disable();
                        break;
                    case 'toggle':
                        $entity->isEnabled() ? $entity->disable() : $entity->enable();
                        break;
                }
                $manager->flush();
            } else {
                throw new FormException($form);
            }
        }

        return $entity;
    }
}
