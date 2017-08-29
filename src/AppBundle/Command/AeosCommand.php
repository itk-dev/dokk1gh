<?php

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
    private function listIdentifiers()
    {
    }

    /**
     * @command
     */
    private function cleanUpIdentifiers()
    {
    }
}
