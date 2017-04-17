<?php

namespace IdleBundle\Form;

use Doctrine\ORM\EntityRepository;
use IdleBundle\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemCreated', EntityType::class, array(
                'class' => Item::class,
//                'choice_label' => 'name',
                'placeholder' => "Choose the item",
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.typeItem', 'ti')
                        ->where('ti.name != \'Recipe\'') // TODO : Remove if want to craft recipes
                        ->orderBy('ti.name', 'ASC')
                        ->addOrderBy('i.name', 'ASC');
                }))
            ->add('crafts', CollectionType::class, array(
                'entry_type' => CraftType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
//                'prototype_data' => 'Add Craft',
//                'entry_options' => array('attr' => array('class' => 'name'))
            ))
//            ->add('item', ItemType::class)
            ->add('save', SubmitType::class, array('label' => 'Save Recipe'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\Recipe'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_recipe';
    }


}
