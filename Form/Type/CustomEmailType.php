<?php
namespace MauticPlugin\CustomEmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;

class CustomEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Optional: choose an existing Mautic Email by ID to render tokens, etc.
            ->add('email', IntegerType::class, [
                'label'    => 'Mautic Email ID (optional)',
                'required' => false,
            ])
            ->add('subject', TextType::class, [
                'label'    => 'Subject',
                'required' => true,
            ])
            ->add('body', TextareaType::class, [
                'label'    => 'Body',
                'required' => true,
            ])
            ->add('startDate', DateTimeType::class, [
                'label'    => 'Start Date',
                'required' => false,
                'widget'   => 'single_text',
                'html5'    => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'label'    => 'End Date',
                'required' => false,
                'widget'   => 'single_text',
                'html5'    => false,
            ])
            ->add('sending_speed_unit', ChoiceType::class, [
                'label'   => 'Sending Speed Unit',
                'choices' => ['Seconds' => 'seconds', 'Minutes' => 'minutes'],
                'required' => false,
            ])
            ->add('sending_speed_value', IntegerType::class, [
                'label'    => 'Delay Value',
                'required' => false,
            ])
            ->add('daily_limit', IntegerType::class, [
                'label'    => 'Daily Limit',
                'required' => false,
            ])
            ->add('daily_increment', IntegerType::class, [
                'label'    => 'Daily Increment (%)',
                'required' => false,
            ]);

        // Transform single_text datetime to string and back
        foreach (['startDate','endDate'] as $dt) {
            $builder->get($dt)->addModelTransformer(new CallbackTransformer(
                fn ($dateTime) => $dateTime instanceof \DateTimeInterface ? $dateTime->format('Y-m-d H:i') : null,
                fn ($stringDate) => $stringDate instanceof \DateTimeInterface ? $stringDate : ($stringDate ? new \DateTime($stringDate) : null)
            ));
        }
    }

    public function getBlockPrefix()
    {
        return 'customemail_action';
    }
}
