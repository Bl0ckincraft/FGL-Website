<?php

namespace App\Form;

use App\Entity\News;
use App\Entity\NewsLayout;
use App\Entity\NewsText;
use App\Form\Transformer\DateTimeToTimestampTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsFormType extends AbstractType
{
    public function __construct(private DateTimeToTimestampTransformer $transformer)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', NewsTextFormType::class, [
                'label' => 'Titre',
                'label_attr' => [
                    "class" => "main-field-label"
                ],
                'required' => true,
            ])
            ->add('description', NewsTextFormType::class, [
                'label' => 'Description',
                'label_attr' => [
                    "class" => "main-field-label"
                ],
                'required' => true,
            ])
            ->add('layout', NewsLayoutFormType::class, [
                'label' => 'Mise en page',
                'label_attr' => [
                    "class" => "main-field-label"
                ],
                'required' => true,
            ])
            ->add('epoch_millis', DateTimeType::class, [
                'label' => 'Date',
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
                'label_attr' => [
                    "class" => "main-field-label"
                ],
            ])
            ->get('epoch_millis')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
