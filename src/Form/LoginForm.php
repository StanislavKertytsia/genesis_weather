<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class LoginForm extends AbstractType
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
            ]);
    }
}