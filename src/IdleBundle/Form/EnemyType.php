<?php

namespace IdleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnemyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'attr' => array('placeholder' => "Name")))
            ->add('minFieldLevel', NumberType::class, array(
                'attr' => array('placeholder' => "Minimum Field Level",
                    'title' => "Minimum Field Level")
            ))
            ->add('maxFieldLevel', NumberType::class, array(
                'attr' => array(
                    'placeholder' => "Maximum Field Level",
                    'title' => "Maximum Field Level")
            ))
            ->add('image', FileType::class, array(
                'label' => 'Image (PNG file)',
                'required' => false))
            ->add('characteristics', CharacteristicsType::class)
            ->add('loots', CollectionType::class, array(
                'entry_type' => LootType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
            ))
            ->add('save', SubmitType::class, array('label' => 'Save Enemy'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\Enemy'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_enemy';
    }


}
