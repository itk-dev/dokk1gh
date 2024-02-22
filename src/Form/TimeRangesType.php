<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Form;

use App\Service\Configuration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimeRangesType extends AbstractType
{
    private static $weekDayNames = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public function __construct(
        private readonly Configuration $configuration,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // A pseudo field used only for general error messages.
        $builder
            ->add('message', TextType::class, [
                'required' => false,
                'label_attr' => [
                    'style' => 'display: none',
                ],
                'attr' => [
                    'style' => 'display: none',
                ],
            ]);

        $timeOptions = $this->getTimeOptions();
        $defaultValues = $this->getDefaultValues();
        $builder->add('default_values', HiddenType::class, [
            'data' => json_encode($defaultValues),
            'mapped' => false,
        ]);

        $startTimeChoices = array_combine($timeOptions, $timeOptions);
        array_pop($startTimeChoices);
        $endTimeChoices = array_combine($timeOptions, $timeOptions);
        array_shift($endTimeChoices);

        foreach (self::$weekDayNames as $day => $name) {
            $builder
                ->add('start_time_'.$day, ChoiceType::class, [
                    'required' => false,
                    'choices' => $startTimeChoices,
                    'label' => 'Start time '.$name,
                ])
                ->add('end_time_'.$day, ChoiceType::class, [
                    'required' => false,
                    'choices' => $endTimeChoices,
                    'label' => false,
                ]);
        }

        $builder
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $numberOfTimeIntervals = 0;
                foreach (self::$weekDayNames as $day => $name) {
                    $startTime = $form->get('start_time_'.$day);
                    $endTime = $form->get('end_time_'.$day);

                    if ($startTime->getData() && !$endTime->getData()) {
                        $endTime->addError($this->createFormError('Please specify an end time'));
                    } elseif (!$startTime->getData() && $endTime->getData()) {
                        $startTime->addError($this->createFormError('Please specify a start time'));
                    } elseif ($startTime->getData() && $endTime->getData()
                        && $endTime->getData() <= $startTime->getData()) {
                        $endTime->addError($this->createFormError('End time must be after start time'));
                    } elseif ($startTime->getData() && $endTime->getData()) {
                        ++$numberOfTimeIntervals;
                    }
                }
                if (0 === $numberOfTimeIntervals) {
                    $form->get('message')->addError($this->createFormError('Please specify at least one time interval'));
                }
            });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'view_timezone' => 'GMT',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_time_ranges';
    }

    private function getTimeOptions()
    {
        $min = $this->configuration->get('guest_timeRanges_min');
        $max = $this->configuration->get('guest_timeRanges_max');
        $step = $this->configuration->get('guest_timeRanges_step');
        $step = new \DateInterval('PT'.strtoupper((string) $step));

        // Make sure that we don't hit a leap day.
        $minDate = \DateTime::createFromFormat('Y-m-d H:i', '2001-01-01 '.$min);
        $maxDate = \DateTime::createFromFormat('Y-m-d H:i', '2001-01-01 '.$max);

        $choices = [];
        while ($minDate <= $maxDate) {
            $choices[] = $minDate->format('H:i');
            $minDate->add($step);
        }

        return $choices;
    }

    private function getDefaultValues()
    {
        return $this->configuration->get('guest_default_timeRanges');
    }

    private function createFormError(string $message, array $parameters = [])
    {
        return new FormError($this->translator->trans($message, $parameters, 'validators'));
    }
}
