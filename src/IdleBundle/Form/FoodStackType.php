<?php

namespace IdleBundle\Form;

use Doctrine\ORM\EntityRepository;
use IdleBundle\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FoodStackType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', NumberType::class, array(
                'label' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'title' => "Quantity")
            ))
//            ->add('item', EntityType::class, array(
//                'class' => Item::class,
////                'choice_label' => 'name',
//                'placeholder' => "Choose the food",
//                'attr' => array(
//                    'class' => 'form-control'),
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('i')
//                        ->innerJoin('i.typeItem', 'ti')
//                        ->where('ti.name = \'Food\'')
//                        ->orderBy('ti.name', 'ASC')
//                        ->addOrderBy('i.name', 'ASC');
//                }))
//            ->add('hero')
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\FoodStack'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_foodstack';
    }


}
