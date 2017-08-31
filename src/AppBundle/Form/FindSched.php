<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use AppBundle\Entity\TownCities;

class FindSched extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('origin', EntityType::class, [
                'label'         => false,
                'required'      => true,
                'class'         => TownCities::class,
                'choice_label'  => function($city) {
                    if($city) {
                        return sprintf("%s (%s)",
                            $city->getTownCity(),
                            $city->getProvince()
                        );
                    }
                },
                'choice_value'  => function($city) {
                    if($city) {
                        return $city->getId();
                    }
                },
                'placeholder'   => 'Your city/town origin'
            ])
            ->add('destination', EntityType::class, [
                'label'         => false,
                'required'      => true,
                'class'         => TownCities::class,
                'choice_label'  => function($city) {
                    if($city) {
                        return sprintf("%s (%s)",
                            $city->getTownCity(),
                            $city->getProvince()
                        );
                    }
                },
                'choice_value'  => function($city) {
                    if($city) {
                        return $city->getId();
                    }
                },
                'placeholder'   => 'Travelling to city/town'
            ])
            ->add('date', TextType::class, [
                'label'         => false,
                'required'  => true,
                'attr' => [
                    'placeholder' => 'When will be your travel date?'
                ]
            ])
        ;
    }

    public function getName()
    {
        return 'find_sched';
    }

}
