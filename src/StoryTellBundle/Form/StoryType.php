<?php

namespace StoryTellBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoryType extends AbstractType
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
            ->add('genres', CollectionType::class, array(
                
            ))
            /*->add('storyGenre', EntityType::class, array(
                    'class' => 'StoryTellBundle:StoryGenre',
                    'choice_label' => 'name',
                    'placeholder' => "Choose the story genre",
                    'choice_translation_domain' => 'messages',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('sg')
                            ->orderBy('sg.name', 'ASC');
                    })
            )*/
            ->add('language', EntityType::class, array(
                    'class' => 'AppBundle:Language',
                    'choice_label' => 'name',
                    'placeholder' => "Choose the language",
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('lan')
                            ->orderBy('lan.id', 'ASC');
                    })
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
