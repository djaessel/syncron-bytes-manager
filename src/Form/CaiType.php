<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Type;

class CaiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('images', FileType::class, [
                'label' => 'Images for merge',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // only for visiblity, not for validation
                'attr' => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ],

                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => false,

                // for multiple images (necessary for merging)
                'multiple' => true,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                // FIXME: validate in controller ?!
                // 'constraints' => [
                //     new File([
                //         'maxSize' => '16M',
                //         'mimeTypes' => [
                //             'image/bmp',
                //             'image/jpeg',
                //             'image/png',
                //         ],
                //         'mimeTypesMessage' => 'Please upload a valid and supported image format',
                //     ]),
                //     new Type([
                //       'type'=>"File",
                //       'message'=>"The value {{ value }} is not a valid {{ type }}."
                //     ]),
                // ],
            ])
        ;
    }
}
