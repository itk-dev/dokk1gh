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

class ExpireInactiveAppsCommand extends Command
{
    protected static $defaultName = 'app:expire-inactive-apps';

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
Expire Guest apps that have been sent before the specified time, but have not
been activated.  Expiring an app will anonymize the data and set the expired
at property to `now`.
EOF
            )
            ->addOption(
                'app-sent-before',
                null,
                InputOption::VALUE_REQUIRED,
                'Process apps that have not been activated within this time period',
                '-8 hours'
            )
            ->addArgument('ids', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Ids to process');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $appSentBeforeSpec = $input->getOption('app-sent-before');
        $appSentBefore = null;
        $ids = $input->getArgument('ids');

        try {
            $appSentBefore = new \DateTime($appSentBeforeSpec);
        } catch (\Exception $exception) {
        }
        if (null === $appSentBefore) {
            throw new RuntimeException('Invalid end-time-before: '.$appSentBeforeSpec);
        }
        if ($appSentBefore > new \DateTime()) {
            throw new RuntimeException('end-time-before must be in the past');
        }

        $respository = $this->entityManager->getRepository(Guest::class);

        $query = $respository->createQueryBuilder('e')
            ->andWhere('e.activatedAt IS NULL')
            ->andWhere('e.sentAt IS NOT NULL')
            ->andWhere('e.sentAt < :appSentBefore')
            ->setParameter('appSentBefore', $appSentBefore)
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
