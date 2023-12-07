<?php

namespace Lle\CredentialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DumpCredentialsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('filename', TextType::class, [
            'label' => $this->translator->trans('label.filename', [], 'CredentialBundle', 'fr'),
            'mapped' => false,
            'required' => true,
        ]);
    }
}