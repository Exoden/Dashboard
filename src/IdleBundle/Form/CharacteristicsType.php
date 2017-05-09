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
                'attr' => array('placeholder' => "Damage Minimum",
                    'title' => "Damage Minimum")
            ))
            ->add('damageMaximum', NumberType::class, array(
                    'attr' => array('placeholder' => "Damage Maximum",
                        'title' => "Damage Maximum")
            ))
            ->add('attackDelay', NumberType::class, array(
                    'attr' => array('placeholder' => "Attack Delay",
                        'title' => "Attack Delay")
            ))
            ->add('hitPrecision', NumberType::class, array(
                    'attr' => array('placeholder' => "Hit Precision",
                        'title' => "Hit Precision")
            ))
            ->add('health', NumberType::class, array(
                    'attr' => array('placeholder' => "Health",
                        'title' => "Health")
            ))
            ->add('armor', NumberType::class, array(
                    'attr' => array('placeholder' => "Armor",
                        'title' => "Armor")
            ))
            ->add('dodge', NumberType::class, array(
                    'attr' => array('placeholder' => "Dodge",
                        'title' => "Dodge")
            ))
            ->add('criticalChance', NumberType::class, array(
                    'attr' => array('placeholder' => "Critical Chance",
                        'title' => "Critical Chance")
            ))
            ->add('blocking', NumberType::class, array(
                    'attr' => array('placeholder' => "Blocking",
                        'title' => "Blocking")
            ));
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
