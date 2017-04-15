<?php

namespace IdleBundle\Form;

use IdleBundle\Entity\TypeStuff;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StuffType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item', ItemType::class)
            ->add('type', EntityType::class, array(
                'class' => TypeStuff::class,
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false,
                'choice_translation_domain' => 'messages',
            ))
            ->add('characteristics', CharacteristicsType::class)
            ->add('save', SubmitType::class, array('label' => 'Save Stuff'))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\Stuff',
            'cascade_validation' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_stuff';
    }


}
