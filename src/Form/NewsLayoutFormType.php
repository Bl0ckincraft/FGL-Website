<?php

namespace App\Form;

use App\Entity\NewsLayout;
use App\Form\Transformer\DateTimeToTimestampTransformer;
use App\Form\Transformer\NewsImageTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class NewsLayoutFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image_temp', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'accept' => 'image/png'
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Veuillez entrer un fichier .png',
                    ])
                ],
            ])
            ->add('text_percentage', IntegerType::class, [
                'label' => 'Pourcentage du texte',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('text_alignment', ChoiceType::class, [
                'label' => 'Alignement du texte',
                'choices' => [
                    'Gauche' => 0,
                    'Centre' => 1,
                    'Droite' => 2,
                ],
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ])
            ->add('min_height', IntegerType::class, [
                'label' => 'Hauteur minimum',
                'required' => true,
            ])
            ->add('max_width', IntegerType::class, [
                'label' => 'Largeur maximum',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewsLayout::class,
        ]);
    }
}
