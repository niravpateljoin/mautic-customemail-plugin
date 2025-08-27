<?php
namespace MauticPlugin\CustomEmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;

class CustomEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'widget'   => 'single_text',  // Use single text input
                'html5'    => false,          // Disable HTML5 date/time picker
            ])
            ->add('endDate', DateTimeType::class, [
                'label'    => 'End Date',
                'required' => false,
                'widget'   => 'single_text',  // Use single text input
                'html5'    => false,          // Disable HTML5 date/time picker
            ])
            ->add('sending_speed_unit', ChoiceType::class, [
                'label'   => 'Sending Speed Unit',
                'choices' => [
                    'Seconds' => 'seconds',
                    'Minutes' => 'minutes',
                ],
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

        $builder->get('startDate')
            ->addModelTransformer(new CallbackTransformer(
                function ($dateTime) {
                    return $dateTime instanceof \DateTime ? $dateTime->format('Y-m-d H:i') : null; // Model -> View
                },
                function ($stringDate) {
                    return $stringDate instanceof \DateTime ? $stringDate : new \DateTime($stringDate); // View -> Model
                }
            ));

        $builder->get('endDate')
            ->addModelTransformer(new CallbackTransformer(
                function ($dateTime) {
                    return $dateTime instanceof \DateTime ? $dateTime->format('Y-m-d H:i') : null; // Model -> View
                },
                function ($stringDate) {
                    return $stringDate instanceof \DateTime ? $stringDate : new \DateTime($stringDate); // View -> Model
                }
            ));


        }

    public function getBlockPrefix()
    {
        return 'customemail_action';
    }
}
