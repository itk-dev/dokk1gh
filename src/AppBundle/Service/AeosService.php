<?php

namespace AppBundle\Service;

use AppBundle\Helpers\CarrierStates;

class AeosService
{
    private $configuration;

    // Debugging.
    private $lastRequest;
    private $lastResponse;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    private function invoke($method)
    {
        $context = stream_context_create([
            'ssl' => [
                // set some SSL/TLS specific options
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $location = $this->configuration['location'];
        $username = $this->configuration['username'];
        $password = $this->configuration['password'];
        $debug = isset($this->configuration['debug']) ? $this->configuration['debug'] : false;

        $client = new \SoapClient($location . '?wsdl', [
            'location' => $location,
            'login' => $username,
            'password' => $password,
            'stream_context' => $context,
            'trace' => $debug,
        ]);

        $result = null;
        if (func_num_args() > 1) {
            $arguments = array_slice(func_get_args(), 1);
            $result = call_user_func_array([$client, $method], $arguments);
        } else {
            $result = call_user_func([$client, $method]);
        }

        if ($debug) {
            $this->lastRequest = $client->__getLastRequest();
            $this->lastResponse = $client->__getLastResponse();
        }

        return $result;
    }

    public function setVerificationState($carrier, bool $activated)
    {
        return $this->setCarrierState($carrier, CarrierStates::ACTIVATE_VERIFICATION, $activated);
    }

    public function setCarrierState($carrier, string $state, bool $activated)
    {
        return $this->invoke('changeCarrierAttribute', (object)[
            'CarrierId' => $carrier->Id,
            'State' => $state,
            'Activated' => $activated,
        ]);
    }

    public function getCarrierStates($carrier)
    {
        return $this->invoke('findCarrierStates', $carrier->Id);
    }

    public function getIdentifiers(array $query = [])
    {
        return $this->invoke('findToken', (object)['IdentifierSearch' => $query]);
    }

    public function getVisitors(array $query = [])
    {
        return $this->invoke('findVisitor', (object)['VisitorInfo' => $query]);
    }

    public function getVisitor($id)
    {
        $visitors = $this->getVisitors(['Id' => $id]);

        return $visitors;
    }

    public function deleteVisitor($visitor)
    {
        $result = $this->invoke('removeVisitor', $visitor->Id);

        return $result;
    }

    public function getVisits(array $query = [])
    {
        return $this->invoke('findVisit', (object)$query);
    }

    public function deleteVisit($visit)
    {
        return $this->invoke('removeVisit', $visit->Id);
    }

    public function createVisitor(array $data)
    {
        return (object)$this->invoke('addVisitor', $data);
    }

    public function createIdentifier($visitor, array $data)
    {
        $data += [
            'CarrierId' => $visitor->Id,
        ];
        return (object)$this->invoke('assignToken', $data);
    }

    public function updateVisitor($id, array $data)
    {
        $data['Id'] = $id;
        return $this->invoke('changeVisitor', $data);
    }

    public function createVisit($visitor, $contactPerson, \DateTime $beginVisit, \DateTime $endVisit, $template)
    {
        $dateFormat = 'Y-m-d\TH:i:s';
        $startTime = $beginVisit->format($dateFormat);
        $endTime = $endVisit->format($dateFormat);
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

        return $this->invoke('addVisit', (object)$data);
    }

    public function getUnits(array $query = [])
    {
        return $this->invoke('findUnit', (object)['UnitSearchInfo' => $query]);
    }

    public function getPersons(array $query = [])
    {
        return $this->invoke('findPerson', (object)['PersonInfo' => $query]);
    }

    public function getTemplates(array $query = [])
    {
        $query += [
            'UnitOfAuthType' => 'OnLine',
        ];
        return $this->invoke('findTemplate', (object)['TemplateInfo' => $query]);
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
