<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Entity\Guest;
use App\Service\GuestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireGuestsCommand extends Command
{
    protected static $defaultName = 'app:expire-guests';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GuestService */
    private $guestService;

    public function __construct(EntityManagerInterface $entityManager, GuestService $guestService)
    {
        $this->entityManager = $entityManager;
        $this->guestService = $guestService;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setDescription(
                <<<'EOF'
Expire Guests whose end time is before a specified time. Expiring a guest will
anonymize the data and set the expired at property to `now`.
EOF
            )
            ->addOption(
                'end-time-before',
                null,
                InputOption::VALUE_REQUIRED,
                'Process Guests whose end time is before this time',
                '-30 days'
            )
            ->addArgument('ids', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Ids to process');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $endTimeBeforeSpec = $input->getOption('end-time-before');
        $endTimeBefore = null;
        $ids = $input->getArgument('ids');

        try {
            $endTimeBefore = new \DateTime($endTimeBeforeSpec);
        } catch (\Exception $exception) {
        }
        if (null === $endTimeBefore) {
            throw new RuntimeException('Invalid end-time-before: '.$endTimeBeforeSpec);
        }
        if ($endTimeBefore > new \DateTime()) {
            throw new RuntimeException('end-time-before must be in the past');
        }

        $respository = $this->entityManager->getRepository(Guest::class);

        $query = $respository->createQueryBuilder('e')
            ->andWhere('e.expiredAt IS NULL')
            ->andWhere('e.endTime < :endTimeBefore')
            ->setParameter('endTimeBefore', $endTimeBefore)
            ->getQuery();

        $entities = $query->execute();

        // Add identified guests.
        foreach ($ids as $id) {
            $guest = $respository->find($id);
            if (null === $guest) {
                throw new \RuntimeException('Invalid guest id: '.$id);
            }
            $entities[] = $guest;
        }

        foreach ($entities as $entity) {
            if ($this->guestService->expire($entity)) {
                if ($output->isVerbose()) {
                    $output->writeln([$entity->getId()]);
                }
            }
        }
    }
}
