<?php

namespace IdleBundle\Form;

use Doctrine\ORM\EntityRepository;
use IdleBundle\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LootType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('enemy')
            ->add('item', EntityType::class, array(
                'class' => Item::class,
//                'choice_label' => 'name',
                'placeholder' => "Choose the item",
                'attr' => array(
                    'class' => 'form-control'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.typeItem', 'ti')
                        ->orderBy('ti.name', 'ASC')
                        ->addOrderBy('i.name', 'ASC');
                }))
            ->add('percent', NumberType::class, array(
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => "Percent",
                    'title' => "Percent")
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\Loot'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_loot';
    }


}
