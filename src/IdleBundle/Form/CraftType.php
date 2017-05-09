<?php

namespace IdleBundle\Form;

use Doctrine\ORM\EntityRepository;
use IdleBundle\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CraftType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('recipe', RecipeType::class)
            ->add('itemNeeded', EntityType::class, array(
                'class' => Item::class,
//                'choice_label' => 'name',
                'placeholder' => "Choose an item",
                'attr' => array(
                    'class' => 'form-control'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('i')
                        ->innerJoin('i.typeItem', 'ti')
                        ->where('ti.name != \'Recipe\'')
                        ->orderBy('ti.name', 'ASC')
                        ->addOrderBy('i.name', 'ASC');
                }))
            ->add('quantity', IntegerType::class, array(
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => "Quantity")
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\Craft'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_craft';
    }


}
