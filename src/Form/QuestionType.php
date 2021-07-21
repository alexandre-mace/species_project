<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rate', ChoiceType::class, [
                'choices'  => [
                    'A ++++' => 1,
                    'A +++' => 2,
                    'A ++' => 3,
                    'A +' => 4,
                    'A = B' => 5,
                    'B +' => 6,
                    'B ++' => 7,
                    'B +++' => 8,
                    'B ++++' => 9,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('speciesA', HiddenType::class, [
                'data' => $options['speciesA']
            ])
            ->add('speciesB', HiddenType::class, [
                'data' => $options['speciesB']
            ])
            ->add('Valider', SubmitType::class, [
                'attr' => [
                    'class' => 'btn mt-3 d-flex mx-auto fs-1-2'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'speciesA' => null,
            'speciesB' => null
        ]);
    }
}
