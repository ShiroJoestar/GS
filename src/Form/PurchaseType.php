<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Purchase;
use PhpParser\Node\Stmt\Label;
use Symfony\Component\Form\AbstractType;
use App\Form\EchantillonProductType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class)
           
            
            ->add('paymentMethod' ,TextType::class)
            ->add('client', EntityType::class,[
                'class' => Client::class,
                'choice_label' => 'name',
            ])
            ->add('product', CollectionType::class, [
                'entry_type' => EchantillonProductType::class,
                'allow_add' => true,
                'prototype' => true,
                
            ])
        ;
        $builder->get('date')->addModelTransformer(new CallbackTransformer(
            function ($value) {
                if(!$value) {
                    return new \DateTime();
                }
                return $value;
            },
            function ($value) {
                return $value;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Purchase::class,
        ]);
    }
}
