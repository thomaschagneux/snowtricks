<?php

namespace App\Form;

use App\Entity\Figure;
use App\Entity\FigureGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FigureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'Description courte',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('figureGroup', EntityType::class, [
                'label' => 'Groupe de figure',
                'class' => FigureGroup::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);

        if ('add' == $options['action']) {
            $builder
                ->add('newMedia', MediaType::class, [
                    'label' => 'Ajouter un média',
                    'mapped' => false,
                    'required' => false,
                ])
                ->add('newFeaturedMedia', MediaType::class, [
                    'label' => 'Ajouter un média à la une',
                    'mapped' => false,
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Figure::class,
            'action' => 'add',
        ]);
    }
}
