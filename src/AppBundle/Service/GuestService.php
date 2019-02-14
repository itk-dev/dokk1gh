<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Service;

use AppBundle\Entity\Code;
use AppBundle\Entity\Guest;
use AppBundle\Entity\Template;
use AppBundle\Exception\GuestException;
use AppBundle\Exception\InvalidTemplateException;
use Doctrine\ORM\EntityManagerInterface;
use Superbrave\GdprBundle\Anonymize\Anonymizer;

class GuestService
{
    /** @var AeosHelper */
    private $aeosHelper;

    /** @var EntityManagerInterface */
    private $manager;

    /** @var Anonymizer */
    private $anonymizer;

    /** @var TwigHelper */
    private $twig;

    /** @var Configuration */
    private $configuration;

    /** @var SmsHelper */
    private $smsHelper;

    /** @var MailerInterface */
    private $mailHelper;

    public function __construct(
        AeosHelper $aeosHelper,
        EntityManagerInterface $manager,
        Anonymizer $anonymizer,
        TwigHelper $twig,
        Configuration $configuration,
        SmsHelper $smsHelper,
        MailHelper $mailHelper
    ) {
        $this->aeosHelper = $aeosHelper;
        $this->manager = $manager;
        $this->anonymizer = $anonymizer;
        $this->twig = $twig;
        $this->configuration = $configuration;
        $this->smsHelper = $smsHelper;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Create a new guest with default values.
     *
     * @return Guest
     */
    public function createNewGuest()
    {
        $guest = new Guest();
        $guest
            ->setEnabled(true)
            ->setPhoneContryCode($this->configuration->get('guest_default_phone_country_code'))
            ->setStartTime(new \DateTime($this->configuration->get('guest_default_startTime')))
            ->setEndTime(new \DateTime($this->configuration->get('guest_default_endTime')));

        return $guest;
    }

    /**
     * Send app url to user via sms and email.
     *
     * @param Guest  $guest
     * @param string $appUrl
     *
     * @return bool
     */
    public function sendApp(Guest $guest, $appUrl)
    {
        if (null !== $guest->getPhone()) {
            $this->smsHelper->sendApp($guest, $appUrl);
        }
        if (null !== $guest->getEmail()) {
            $this->mailHelper->sendApp($guest, $appUrl);
        }

        $guest->setSentAt(new \DateTime());
        $this->manager->persist($guest);
        $this->manager->flush();

        return true;
    }

    public function activate(Guest $guest)
    {
        $guest->setActivatedAt(new \DateTime());
        $this->manager->persist($guest);
        $this->manager->flush();
    }

    public function isValid(Guest $guest)
    {
        $now = new \DateTime();

        return $guest->isEnabled()
            && null !== $guest->getActivatedAt()
            && $guest->getActivatedAt() <= $now
            && $guest->getStartTime() <= $now
            && $now <= $guest->getEndTime();
    }

    public function canRequestCode(Guest $guest)
    {
        if (!$this->isValid($guest)) {
            return false;
        }

        $timezone = new \DateTimeZone($this->configuration->get('view_timezone'));
        $now = new \DateTime('now', $timezone);

        $timeRanges = $guest->getTimeRanges();
        $day = $now->format('N');
        if (!isset($timeRanges['start_time_'.$day], $timeRanges['end_time_'.$day])
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', $timeRanges['start_time_'.$day], $startTimeData)
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', $timeRanges['end_time_'.$day], $endTimeData)) {
            return false;
        }

        $startTime = clone $now;
        $startTime->setTime($startTimeData['hours'], $startTimeData['minutes']);
        $endTime = clone $now;
        $endTime->setTime($endTimeData['hours'], $endTimeData['minutes']);

        if ($now < $startTime || $endTime < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get end time for a new code.
     */
    public function getEndTime(Guest $guest)
    {
        $duration = $this->configuration->get('guest_code_duration');
        if (null !== $duration) {
            // Relative end time.
            return new \DateTime($duration);
        }

        // Use end time from guest access times.
        $timezone = new \DateTimeZone($this->configuration->get('view_timezone'));
        $now = new \DateTime('now', $timezone);
        $timeRanges = $guest->getTimeRanges();
        $day = $now->format('N');

        if (!isset($timeRanges['start_time_'.$day], $timeRanges['end_time_'.$day])
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', $timeRanges['start_time_'.$day], $startTimeData)
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', $timeRanges['end_time_'.$day], $endTimeData)) {
            return null;
        }

        $endTime = clone $now;
        $endTime->setTime($endTimeData['hours'], $endTimeData['minutes']);
        $endTime->setTimeZone(new \DateTimeZone('UTC'));

        return $endTime;
    }

    public function generateCode(Guest $guest, Template $template, $note = null)
    {
        if (!$this->canRequestCode($guest)) {
            throw new GuestException('Guest cannot request code right now', [
                'guest' => $guest,
            ]);
        }

        if (!$guest->getTemplates()->contains($template)) {
            throw new InvalidTemplateException('Guest does not have access to template', [
                'guest' => $guest,
                'template' => $template,
            ]);
        }

        try {
            $code = (new Code())
                ->setNote($note)
                ->setCreatedBy($guest->getCreatedBy())
                ->setTemplate($template)
                ->setStartTime(new \DateTime())
                ->setEndTime($this->getEndTime($guest));

            $visitorName = $this->twig
                ->renderTemplate($this->configuration->get('guest_code_name_template'), [
                    'guest' => $guest,
                    'template' => $template,
                ]);

            $this->aeosHelper->createAeosIdentifier($code, $visitorName);
            $this->manager->persist($code);
            $this->manager->flush();

            return $code;
        } catch (\Exception $ex) {
            throw new GuestException('Cannot generate code');
        }
    }

    public function sendCode(Guest $guest, Code $code)
    {
        $this->smsHelper->sendCode($guest, $code);
    }

    /**
     * Expire a Guest by anonymizing data and setting the expired at time.
     *
     * @param Guest $guest
     */
    public function expire(Guest $guest)
    {
        if (null !== $guest && null === $guest->getExpiredAt()) {
            $this->anonymizer->anonymize($guest);
            $guest
                ->setEnabled(false)
                ->setExpiredAt(new \DateTime());
            $this->manager->persist($guest);
            $this->manager->flush();

            return true;
        }

        return false;
    }
}
