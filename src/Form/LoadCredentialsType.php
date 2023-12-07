<?php

namespace Lle\CredentialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoadCredentialsType extends AbstractType
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('filename', TextType::class, [
            'label' => $this->translator->trans('label.filename', [], 'CredentialBundle', 'fr'),
            'mapped' => false,
            'required' => true,
        ]);
        $builder->add('file', FileType::class, [
            'label' => $this->translator->trans('label.file', [], 'CredentialBundle', 'fr'),
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'mimeTypes' => [
                        'application/json',
                    ],
                    'mimeTypesMessage' => $this->translator->trans('text.file_type_error'),
                ]),
            ],
        ]);
    }
}
