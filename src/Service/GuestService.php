<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Code;
use App\Entity\Guest;
use App\Entity\Template;
use App\Exception\GuestException;
use App\Exception\InvalidTemplateException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GuestService
{
    public function __construct(
        private readonly AeosHelper $aeosHelper,
        private readonly EntityManagerInterface $manager,
        private readonly TwigHelper $twig,
        private readonly Configuration $configuration,
        private readonly SmsHelper $smsHelper,
        private readonly MailHelper $mailHelper,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
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
            ->setPhoneCountryCode($this->configuration->get('guest_default_phone_country_code'))
            ->setStartTime(new \DateTime($this->configuration->get('guest_default_startTime')))
            ->setEndTime(new \DateTime($this->configuration->get('guest_default_endTime')));

        return $guest;
    }

    /**
     * Send app url to user via sms and email.
     *
     * @return bool
     */
    public function sendApp(Guest $guest)
    {
        $appUrl = $this->urlGenerator->generate('app_code', ['guest' => $guest->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

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
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', (string) $timeRanges['start_time_'.$day], $startTimeData)
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', (string) $timeRanges['end_time_'.$day], $endTimeData)) {
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
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', (string) $timeRanges['start_time_'.$day], $startTimeData)
            || !preg_match('/^(?<hours>\d{2}):(?<minutes>\d{2})$/', (string) $timeRanges['end_time_'.$day], $endTimeData)) {
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
            throw new GuestException('Guest cannot request code right now', ['guest' => $guest]);
        }

        if (!$guest->getTemplates()->contains($template)) {
            throw new InvalidTemplateException('Guest does not have access to template', ['guest' => $guest, 'template' => $template]);
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
        } catch (\Exception) {
            throw new GuestException('Cannot generate code');
        }
    }

    public function sendCode(Guest $guest, Code $code)
    {
        $this->smsHelper->sendCode($guest, $code);
    }

    /**
     * Expire a Guest by anonymizing data and setting the expired at time.
     */
    public function expire(Guest $guest)
    {
        if (null !== $guest && null === $guest->getExpiredAt()) {
            // Anonymize guest.
            $guest
                ->setName($guest->getId())
                ->setCompany($guest->getId())
                ->setPhone($guest->getId())
                ->setPhoneCountryCode('+45')
                ->setEmail($guest->getId().'@example.com');

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
