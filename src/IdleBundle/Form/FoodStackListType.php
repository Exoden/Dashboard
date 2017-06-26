<?php

namespace IdleBundle\Form;

use Doctrine\ORM\EntityRepository;
use IdleBundle\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FoodStackListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('foodStackList', CollectionType::class, array(
                'entry_type' => FoodStackType::class,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'prototype' => true,
//                'by_reference' => false,
            ))
            ->add('save', SubmitType::class, array('label' => 'Save'))
        ;
    }
    
//    /**
//     * {@inheritdoc}
//     */
//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $resolver->setDefaults(array(
//            'data_class' => 'IdleBundle\Entity\FoodStack'
//        ));
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getBlockPrefix()
//    {
//        return 'idlebundle_foodstack';
//    }


}
