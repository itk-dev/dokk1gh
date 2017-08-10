<?php

namespace AppBundle\Mock\Service;

use Symfony\Component\Yaml\Yaml;

class AeosWebService
{
    public function addVisit($params)
    {
        $params->Id = 1;
        $params->Authorization->Id = 1;

        return $params;
    }

    public function addVisitor($params)
    {
        return (object)[
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

    public function assignToken($params)
    {
        $params->Id = 1;
        $params->Blocked = false;
        $params->Status = 1;

        return $params;
    }

    public function blockToken($params)
    {
        return $params;
    }

    public function changeCarrierAttribute($params)
    {
        return (object)[
            'CarrierId' => $params->CarrierId,
            // @TODO: Depend on $params->State and $params->Activated
            'States' => (object)[
                'Blocked' => false,
                'ExcludedFromApb' => false,
                'AutoBlockEnabled' => true,
                'Special' => false,
                'Invisible' => false,
                'ExcludedFromVerification' => true,
            ],
        ];
    }

    public function changeVisitor($params)
    {
    }

    public function findCarrierStates($params)
    {
    }

    public function findPerson($params)
    {
        return $this->loadFixture(__FUNCTION__, $params);
    }

    public function findTemplate($params)
    {
        return $this->loadFixture(__FUNCTION__, $params);
    }

    public function findToken($params)
    {
    }

    public function findUnit($params)
    {
        return $this->loadFixture(__FUNCTION__, $params);
    }

    public function findVisit($params)
    {
    }

    public function findVisitor($params)
    {
    }

    public function removeVisit($params)
    {
        return $params;
    }

    public function removeVisitor($params)
    {
        return $params;
    }

    /**
     * Filter by first (only?) property in $params.
     */
    private function filter($data, $params)
    {
        $dataKeys = array_keys(get_object_vars($data));
        if (count($dataKeys) === 1) {
            $dataKey = $dataKeys[0];
            $vars = array_keys(get_object_vars($params));
            if (count($vars) > 0) {
                $filter = $params->{$vars[0]};

                $data->{$dataKey} = array_values(array_filter($data->{$dataKey}, function ($item) use ($filter) {
                    foreach ($filter as $key => $value) {
                        if (isset($item->{$key}) && (
                                // Substring match on string value.
                                (is_string($item->{$key}) && stripos($item->{$key}, $value) === false)
                                // Exact match on non-string value.
                                || (!is_string($item->{$key}) && $item->{$key} != $value))) {
                            return false;
                        }
                    }

                    return true;
                }));
            }
        }

        return $data;
    }

    private function loadFixture($name, $params)
    {
        $fixturePath = __DIR__ . '/fixtures/' . $name . '.yml';
        if (!file_exists($fixturePath)) {
            throw new \Exception('Fixture "' . $fixturePath . '" not found.');
        }

        $content = file_get_contents($fixturePath);
        $result = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);

        if (isset($params->SearchRange)) {
            unset($params->SearchRange);
            $result = $this->filter($result, $params);
        }

        return $result;
    }
}
