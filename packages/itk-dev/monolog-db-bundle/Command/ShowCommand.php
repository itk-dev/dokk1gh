<?php

/*
 * This file is part of itk-dev/monolog-db-bundle.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\MonologDbBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use ItkDev\MonologDbBundle\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowCommand extends ContainerAwareCommand
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

    public function configure()
    {
        $this->setName('itk-dev:monolog-db:show')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of log entries to show')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit number of log entries to show')
            ->addOption('show-types', null, InputOption::VALUE_NONE, 'Show all types with count')
            ->addOption('output-format', null, InputOption::VALUE_REQUIRED, 'Output format (table|csv|json|json-formatted)', 'table');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('show-types')) {
            $this->showTypes($input, $output);
        } else {
            $this->showLog($input, $output);
        }
    }

    private function showTypes(InputInterface $input, OutputInterface $output)
    {
        $queryBuilder = $this->manager->getRepository(LogEntry::class)->createQueryBuilder('e');
        $query = $queryBuilder
            ->select('e.type, count(e.id) type_count')
            ->groupBy('e.type')
            ->orderBy('e.type', 'ASC');
        $result = $query->getQuery()->getResult();

        foreach ($result as $entry) {
            $output->writeln(implode("\t", [
                $entry['type_count'],
                $entry['type'],
            ]));
        }
    }

    private function showLog(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');
        $limit = $input->getOption('limit') ?: 10;
        if ($limit < 1) {
            $limit = 10;
        }

        $queryBuilder = $this->manager->getRepository(LogEntry::class)->createQueryBuilder('e');
        $query = $queryBuilder
            ->setMaxResults($limit)
            ->orderBy('e.createdAt', 'DESC');
        if (null !== $type = $input->getOption('type')) {
            $query->where('e.type = :type');
            $query->setParameter('type', $type);
        }
        $result = $query->getQuery()->getResult();
        $header = ['created_at', 'type'];
        $rows = array_map(function ($entry) {
            return [
                $entry->getCreatedAt()->format(\DateTime::ATOM),
                $entry->getType(),
            ];
        }, $result);

        $format = $input->getOption('output-format');
        switch ($format) {
            case 'csv':
                $handle = fopen('php://temp', 'w');
                fputcsv($handle, $header);
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
                rewind($handle);
                $csv = rtrim(stream_get_contents($handle), PHP_EOL);
                fclose($handle);
                $output->writeln($csv);

                break;
            case 'json':
            case 'json-formatted':
                $options = 'json-formatted' === $format ? JSON_PRETTY_PRINT : 0;
                $json = json_encode(array_map(function ($row) use ($header) {
                    return array_combine($header, $row);
                }, $rows), $options);
                $output->writeln($json);

                break;
            default:
                $table = new Table($output);
                $table
                    ->setHeaders($header)
                    ->setRows($rows);
                $table->render();

                break;
        }
    }
}
