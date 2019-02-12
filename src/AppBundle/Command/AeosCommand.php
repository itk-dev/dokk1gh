<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Command;

use AppBundle\Service\AeosService;
use Doctrine\ORM\EntityManagerInterface;

class AeosCommand extends AbstractBaseCommand
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $entityManager;

    /** @var \AppBundle\Service\AeosService */
    protected $aeosService;

    public function __construct(EntityManagerInterface $entityManager, AeosService $aeosService)
    {
        parent::__construct('app:aeos');
        $this->entityManager = $entityManager;
        $this->aeosService = $aeosService;
    }

    /**
     * List identifiers.
     *
     * @command
     */
    protected function listIdentifiers()
    {
        $serializer = $this->getContainer()->get('jms_serializer');

        $visitors = $this->getVisitors();
        $visits = $this->getVisits($visitors);
        $identifiers = $this->getIdentifiers($visitors);
        $visitorId2visitId = [];
        foreach ($visits as $visit) {
            $visitorId2visitId[$visit->VisitorId] = $visit->Id;
        }

        $this->writeln('#identifiers: '.\count($identifiers));
        foreach ($identifiers as $identifier) {
            // $visitor = $identifier ? $this->aeosService->getVisitorByIdentifier($identifier) : null;
            // $visit = $visitor ? $this->aeosService->getVisitByVisitor($visitor) : null;
            $visitor = $visitors[$identifier->CarrierId];
            $visit = $visits[$visitorId2visitId[$visitor->Id]];
            $this->writeln(var_export([
                'identifier' => $identifier,
                'visitor' => $visitor,
                'visit' => $visit,
            ], true));
        }
    }

    /**
     * @command
     */
    protected function cleanUpIdentifiers()
    {
    }

    private function getVisitors()
    {
        return $this->getItems('visitor', ['LastName' => 'dokk1gh']);
    }

    private function getVisits(array $visitors)
    {
        return array_filter($this->getItems('visit'), function ($visit) use ($visitors) {
            return isset($visit->VisitorId) && isset($visitors[$visit->VisitorId]);
        });
    }

    private function getIdentifiers(array $visitors)
    {
        return array_filter($this->getItems('identifier'), function ($identifier) use ($visitors) {
            return isset($identifier->CarrierId) && isset($visitors[$identifier->CarrierId]);
        });
    }

    private function getItems(string $name, array $query = [])
    {
        $methods = [
            'identifier' => 'getIdentifiers',
            'visit' => 'getVisits',
            'visitor' => 'getVisitors',
        ];
        $method = $methods[$name];

        $items = [];

        $amount = 100;
        $offset = 0;
        $query += ['amount' => $amount, 'offset' => $offset];
        while (true) {
            $query['offset'] = $offset;
            $result = $this->aeosService->{$method}($query);
            if (\is_array($result)) {
                foreach ($result as $item) {
                    $items[$item->Id] = $item;
                }
            }

            if (\count($result) < $amount) {
                break;
            }

            $offset += $amount;
        }

        return $items;
    }
}
