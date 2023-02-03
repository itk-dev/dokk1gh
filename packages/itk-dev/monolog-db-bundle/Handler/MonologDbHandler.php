<?php

/*
 * This file is part of itk-dev/monolog-db-bundle.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\MonologDbBundle\Handler;

use Doctrine\ORM\EntityManagerInterface;
use ItkDev\MonologDbBundle\Entity\LogEntry;
use Monolog\Handler\AbstractProcessingHandler;

class MonologDbHandler extends AbstractProcessingHandler
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function write(array $record)
    {
        $logEntry = new LogEntry();
        $logEntry->setMessage($record['message']);
        $logEntry->setLevel($record['level']);
        $logEntry->setLevelName($record['level_name']);
        $logEntry->setExtra($record['extra']);
        $logEntry->setContext($record['context']);
        if (isset($record['context']['type'])) {
            $logEntry->setType($record['context']['type']);
        }

        $this->manager->persist($logEntry);
        $this->manager->flush();
    }
}
