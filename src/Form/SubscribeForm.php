<?php

namespace App\Form;

use App\Enum\Frequency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SubscribeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'your.email@domain.com'],
                'constraints' => [
                    new assert\Email(['message' => 'Please enter a valid email address']),
                    new Assert\NotBlank(['message' => 'Please enter your email'])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'attr' => ['placeholder' => 'Your city'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your city']),
                ]])
            ->add('frequency', ChoiceType::class, [
                'label' => 'Update frequency',
                'choices' => Frequency::choices(),
                'constraints' => [
                    new Assert\Choice(array_column(Frequency::cases(), 'value')),
                ]]);

    }
}