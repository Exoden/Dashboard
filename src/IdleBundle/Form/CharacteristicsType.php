<?php

namespace IdleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacteristicsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('damageMinimum', NumberType::class, array(
                'attr' => array('placeholder' => "Damage Minimum"))
            )
            ->add('damageMaximum', NumberType::class, array(
                    'attr' => array('placeholder' => "Damage Maximum"))
            )
            ->add('attackDelay', NumberType::class, array(
                    'attr' => array('placeholder' => "Attack Delay"))
            )
            ->add('hitPrecision', NumberType::class, array(
                    'attr' => array('placeholder' => "Hit Precision"))
            )
            ->add('health', NumberType::class, array(
                    'attr' => array('placeholder' => "Health"))
            )
            ->add('armor', NumberType::class, array(
                    'attr' => array('placeholder' => "Armor"))
            )
            ->add('dodge', NumberType::class, array(
                    'attr' => array('placeholder' => "Dodge"))
            )
            ->add('criticalChance', NumberType::class, array(
                    'attr' => array('placeholder' => "Critical Chance"))
            )
            ->add('blocking', NumberType::class, array(
                    'attr' => array('placeholder' => "Blocking"))
            );
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IdleBundle\Entity\Characteristics'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'idlebundle_characteristics';
    }


}
