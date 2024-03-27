<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Service;

use App\Mock\Entity\AeosActionLogEntry;
use Symfony\Component\Yaml\Yaml;

class AeosWebService
{
    public function __construct(private readonly ActionLogManager $logger)
    {
    }

    public function addVisit(object $params): object
    {
        $this->log(__FUNCTION__, $params);

        $params->Id = 1;
        $params->Authorization->Id = 1;

        return $params;
    }

    public function addVisitor(object $params): object
    {
        $this->log(__FUNCTION__, $params);

        return (object) [
            'Id' => 1,
            'CarrierType' => 'Visitor',
            'UnitId' => $params->UnitId,
            'ArrivalDateTime' => (new \DateTime())->format(\DateTime::W3C),
            'LeaveDateTime' => (new \DateTime('+1 hour'))->format(\DateTime::W3C),
            'ReadOnly' => false,
            'LastName' => $params->LastName,
            'Gender' => 'Unknown',
        ];
    }

    public function assignToken(object $params): object
    {
        $this->log(__FUNCTION__, $params);

        $params->Id = 1;
        $params->Blocked = false;
        $params->Status = 1;

        return $params;
    }

    public function blockToken(object $params): object
    {
        return $params;
    }

    public function changeCarrierAttribute(object $params): object
    {
        return (object) [
            'CarrierId' => $params->CarrierId,
            // @TODO: Depend on $params->State and $params->Activated
            'States' => (object) [
                'Blocked' => false,
                'ExcludedFromApb' => false,
                'AutoBlockEnabled' => true,
                'Special' => false,
                'Invisible' => false,
                'ExcludedFromVerification' => true,
            ],
        ];
    }

    public function changeVisitor(object $params): void
    {
    }

    public function findCarrierStates(object $params): void
    {
    }

    public function findPerson(object $params): object
    {
        return $this->loadFixture(__FUNCTION__, $params);
    }

    public function findTemplate(object $params): object
    {
        return $this->loadFixture(__FUNCTION__, $params);
    }

    public function findToken(object $params): void
    {
    }

    public function findUnit(object $params): object
    {
        return $this->loadFixture(__FUNCTION__, $params);
    }

    public function findVisit(object $params): void
    {
    }

    public function findVisitor(object $params): void
    {
    }

    public function removeVisit(object $params): object
    {
        return $params;
    }

    public function removeVisitor(object $params): object
    {
        return $params;
    }

    /**
     * Filter by first (only?) property in $params.
     */
    private function filter(object $data, object $params): object
    {
        $dataKeys = array_keys(get_object_vars($data));
        if (1 === \count($dataKeys)) {
            $dataKey = $dataKeys[0];
            $vars = array_keys(get_object_vars($params));
            if (\count($vars) > 0) {
                $filter = $params->{$vars[0]};

                $data->{$dataKey} = array_values(array_filter($data->{$dataKey}, function ($item) use ($filter) {
                    foreach ($filter as $key => $value) {
                        if (isset($item->{$key}) && (
                            // Starts with match on string value.
                            (\is_string($item->{$key}) && 0 !== stripos($item->{$key}, (string) $value))
                            // Exact match on non-string value.
                            || (!\is_string($item->{$key}) && $item->{$key} !== $value)
                        )) {
                            return false;
                        }
                    }

                    return true;
                }));
            }
        }

        return $data;
    }

    private function slice(object $data, object $range): object
    {
        $length = $range->nrOfRecords ?? null;
        $offset = $range->startRecordNo ?? 0;

        $dataKeys = array_keys(get_object_vars($data));
        if (1 === \count($dataKeys)) {
            $dataKey = $dataKeys[0];
            $data->{$dataKey} = \array_slice($data->{$dataKey}, $offset, $length);
        }

        return $data;
    }

    private function loadFixture(string $name, object $params): object
    {
        $fixturePath = __DIR__.'/../Resources/aeosws/fixtures/'.$name.'.yml';
        if (!file_exists($fixturePath)) {
            throw new \Exception('Fixture "'.$fixturePath.'" not found.');
        }

        $content = file_get_contents($fixturePath);
        $result = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
        if (isset($params->SearchRange)) {
            $range = $params->SearchRange;
            unset($params->SearchRange);
            $result = $this->filter($result, $params);
            $result = $this->slice($result, $range);
        }

        return $result;
    }

    private function log(string $type, object $data): void
    {
        $this->logger->log(new AeosActionLogEntry($type, (array) $data));
    }
}
