<?php

namespace PickOneBundle\Form;

use Doctrine\ORM\EntityRepository;
use PickOneBundle\Entity\QuestionGenre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                    'attr' => array('placeholder' => "Title"))
            )
            ->add('genres', EntityType::class, array(
                'class' => QuestionGenre::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
                'choice_translation_domain' => 'messages',
            ))
            ->add('language', EntityType::class, array(
                    'class' => 'AppBundle:Language',
                    'choice_label' => 'name',
                    'placeholder' => "Choose the language",
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('lan')
                            ->orderBy('lan.id', 'ASC');
                    })
            )
            ->add('answers', CollectionType::class, array(
                'entry_type' => AnswerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
//                'prototype_data' => 'Add Answer',
//                'entry_options' => array('attr' => array('class' => 'name'))
            ))
            ->add('save', SubmitType::class, array('label' => 'Save Question'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PickOneBundle\Entity\Question'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pickonebundle_question';
    }


}
