<?php

namespace BenTools\EasyFormBundle\Form;

use Symfony\Component\Form\Exception\ExceptionInterface;
use Symfony\Component\Form\FormInterface;
use Throwable;

class FormException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @inheritDoc
     */
    public function __construct(FormInterface $form, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
