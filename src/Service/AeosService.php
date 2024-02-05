<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

class AeosService
{
    final public const ACTIVATE_VERIFICATION = 'ActivateVerification';

    // Debugging.
    private $lastRequest;
    private $lastResponse;

    public function __construct(
        private readonly array $options
    ) {
    }

    public function setVerificationState($carrier, bool $activated)
    {
        return $this->setCarrierState($carrier, self::ACTIVATE_VERIFICATION, $activated);
    }

    public function setCarrierState($carrier, string $state, bool $activated)
    {
        return $this->invoke(
            'changeCarrierAttribute',
            (object) [
                'CarrierId' => $carrier->Id,
                'State' => $state,
                'Activated' => $activated,
            ]
        );
    }

    public function getCarrierStates($carrier)
    {
        return $this->invoke('findCarrierStates', $carrier->Id);
    }

    public function getIdentifiers(array $query = [])
    {
        [$query, $searchRange] = $this->splitQuery($query);
        // findToken requires at least one search value.
        $query += [
            'IdentifierType' => $this->options['aeos']['identifier_type'],
        ];

        $result = $this->invoke('findToken', (object) ['IdentifierSearch' => $query, 'SearchRange' => $searchRange]);
        if (!isset($result->IdentifierAndCarrierId)) {
            return null;
        }

        // Unwrap Identifier and add CarrierId.
        return array_map(function ($item) {
            $value = $item->Identifier;
            $value->CarrierId = $item->CarrierId ?? null;

            return $value;
        }, \is_array($result->IdentifierAndCarrierId)
            ? $result->IdentifierAndCarrierId
            : [$result->IdentifierAndCarrierId]);
    }

    public function getIdentifierByBadgeNumber($badgeNumber)
    {
        $result = $this->getIdentifiers(['BadgeNumber' => $badgeNumber]);

        return ($result && 1 === \count($result)) ? $result[0] : null;
    }

    public function blockIdentifier($identifier)
    {
        $reason = $this->options['aeos']['block_reason'];
        $result = $this->invoke(
            'blockToken',
            (object) [
                'IdentifierType' => $identifier->IdentifierType,
                'BadgeNumber' => $identifier->BadgeNumber,
                'Reason' => $reason,
            ]
        );

        return $result;
    }

    public function isBlocked($identifier)
    {
        return $identifier && isset($identifier->Blocked) && true === $identifier->Blocked;
    }

    public function getVisitors(array $query = [])
    {
        [$query, $searchRange] = $this->splitQuery($query);
        $result = $this->invoke('findVisitor', (object) ['VisitorInfo' => $query, 'SearchRange' => $searchRange]);

        if (!isset($result->Visitor)) {
            return null;
        }

        return array_map(
            fn ($item) => $item->VisitorInfo,
            \is_array($result->Visitor) ? $result->Visitor : [$result->Visitor]
        );
    }

    public function getVisitor($id)
    {
        $result = $this->getVisitors(['Id' => $id]);

        return ($result && 1 === \count($result)) ? $result[0] : null;
    }

    public function getVisitorByIdentifier($identifier)
    {
        return isset($identifier->CarrierId) ? $this->getVisitor($identifier->CarrierId) : null;
    }

    public function deleteVisitor($visitor)
    {
        $result = $this->invoke('removeVisitor', $visitor->Id);

        return $result;
    }

    public function getVisits(array $query = [])
    {
        [$query, $searchRange] = $this->splitQuery($query);
        $query['SearchRange'] = $searchRange;
        $result = $this->invoke('findVisit', (object) $query);

        return !isset($result->Visit) ? null : (\is_array($result->Visit) ? $result->Visit : [$result->Visit]);
    }

    public function getVisit($id)
    {
        $result = $this->getVisits(['Id' => $id]);

        return ($result && 1 === \count($result)) ? $result[0] : null;
    }

    public function getVisitByVisitor($visitor)
    {
        $result = $this->getVisits(['VisitorId' => $visitor->Id]);

        return ($result && 1 === \count($result)) ? $result[0] : null;
    }

    public function deleteVisit($visit)
    {
        return $this->invoke('removeVisit', $visit->Id);
    }

    public function createVisitor(array $data)
    {
        return (object) $this->invoke('addVisitor', $data);
    }

    public function createIdentifier($visitor, $contactPerson)
    {
        $badgeNumber = $this->generateBadgeNumber();
        $data = [
            'IdentifierType' => $this->options['aeos']['identifier_type'],
            'BadgeNumber' => $badgeNumber,
            'UnitId' => $contactPerson->UnitId,
            'CarrierId' => $visitor->Id,
        ];

        return (object) $this->invoke('assignToken', $data);
    }

    public function updateVisitor($id, array $data)
    {
        $data['Id'] = $id;

        return $this->invoke('changeVisitor', $data);
    }

    public function createVisit($visitor, $contactPerson, \DateTime $beginVisit, \DateTime $endVisit, $template)
    {
        $startTime = $this->formatDateTime($beginVisit);
        $endTime = $this->formatDateTime($endVisit);
        $data = [
            'VisitorId' => $visitor->Id,
            'ContactPersonId' => $contactPerson->Id,
            'beginVisit' => $startTime,
            'endVisit' => $endTime,
            'Authorization' => [
                'TemplateAuthorisation' => [
                    'Enabled' => true,
                    'TemplateId' => $template->Id,
                    'DateFrom' => $startTime,
                    'DateUntil' => $endTime,
                ],
            ],
        ];

        return $this->invoke('addVisit', (object) $data);
    }

    public function getUnits(array $query = [])
    {
        [$query, $searchRange] = $this->splitQuery($query);
        $result = $this->invoke('findUnit', (object) ['UnitSearchInfo' => $query, 'SearchRange' => $searchRange]);

        return !isset($result->Unit) ? null : (\is_array($result->Unit) ? $result->Unit : [$result->Unit]);
    }

    public function getPersons(array $query = [])
    {
        [$query, $searchRange] = $this->splitQuery($query);
        $result = $this->invoke('findPerson', (object) ['PersonInfo' => $query, 'SearchRange' => $searchRange]);

        return !isset($result->Person) ? null : (\is_array($result->Person) ? $result->Person : [$result->Person]);
    }

    public function getPerson($id)
    {
        $result = $this->getPersons(['Id' => $id]);

        return ($result && 1 === \count($result)) ? $result[0] : null;
    }

    public function getTemplates(array $query = [])
    {
        [$query, $searchRange] = $this->splitQuery($query);
        $query += [
            'UnitOfAuthType' => 'OnLine',
        ];
        $result = $this->invoke('findTemplate', (object) ['TemplateInfo' => $query, 'SearchRange' => $searchRange]);

        return !isset($result->Template)
            ? null
            : (\is_array($result->Template) ? $result->Template : [$result->Template]);
    }

    public function getTemplate($id)
    {
        $result = $this->getTemplates(['Id' => $id]);

        return ($result && 1 === \count($result)) ? $result[0] : null;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    private function invoke($method)
    {
        $configuration = $this->options['client'];
        $options = $configuration['context'] ?? [];
        $context = stream_context_create($options);

        $location = $configuration['location'];
        $username = $configuration['username'];
        $password = $configuration['password'];
        $debug = $configuration['debug'] ?? false;

        $client = new \SoapClient(
            $location.'?wsdl',
            [
                'location' => $location,
                'login' => $username,
                'password' => $password,
                'stream_context' => $context,
                'trace' => $debug,
            ]
        );

        $result = null;
        if (\func_num_args() > 1) {
            $arguments = \array_slice(\func_get_args(), 1);
            $result = \call_user_func_array([$client, $method], $arguments);
        } else {
            $result = \call_user_func([$client, $method]);
        }

        if ($debug) {
            $this->lastRequest = $client->__getLastRequest();
            $this->lastResponse = $client->__getLastResponse();
        }

        return $result;
    }

    private function generateBadgeNumber($length = null)
    {
        if (null === $length) {
            $length = $this->options['aeos']['identifier_length'] ?? 8;
        }

        // Loop until we find an unused code or time out.
        for ($i = 0; $i < 100; ++$i) {
            // A badge number must start with a non-zero digit.
            $badgeNumber = (string) random_int(1, 9);
            for ($j = 1; $j < $length; ++$j) {
                $badgeNumber .= (string) random_int(0, 9);
            }
            $identifier = $this->getIdentifierByBadgeNumber($badgeNumber);
            if (!$identifier) {
                return $badgeNumber;
            }
        }

        throw new \RuntimeException('Cannot generate unique code');
    }

    private function identifierExists($code)
    {
        return $this->getIdentifierByBadgeNumber($code);
    }

    /**
     * Format date and time for AEOS service.
     *
     * @return string
     */
    private function formatDateTime(\DateTime $date)
    {
        $dateFormat = 'Y-m-d\TH:i:s';
        $date = clone $date;
        $timeZone = new \DateTimeZone($this->options['aeos']['timezone']);
        $date->setTimezone($timeZone);

        return $date->format($dateFormat);
    }

    /**
     * Split a query into a query and a search range.
     */
    private function splitQuery(array $query)
    {
        $searchRange = [
            'nrOfRecords' => 25,
            'startRecordNo' => 0,
        ];

        if (isset($query['amount'])) {
            $searchRange['nrOfRecords'] = $query['amount'];
            unset($query['amount']);
        }
        if (isset($query['offset'])) {
            $searchRange['startRecordNo'] = $query['offset'];
            unset($query['offset']);
        }

        return [$query, $searchRange];
    }
}
