<?php

namespace StoryTellBundle\Form;

use Doctrine\ORM\EntityRepository;
use StoryTellBundle\Entity\StoryGenre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoryEditType extends AbstractType
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
            ->add('description', TextareaType::class, array(
                'attr' => array('placeholder' => "Description", 'rows' => '4'))
            )
            ->add('genres', EntityType::class, array(
                'class' => StoryGenre::class,
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
            ->add('isPublished', CheckboxType::class, array(
                    'label' => "Publish",
                    'required' => false)
            )
            ->add('save', SubmitType::class, array('label' => 'Save Story'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'StoryTellBundle\Entity\Story'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'storytellbundle_story';
    }


}
