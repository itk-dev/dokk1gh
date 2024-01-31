<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Entity;

use App\Mock\Repository\ActionLogEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AeosEntity.
 *
 * @ORM\Entity(repositoryClass=ActionLogEntryRepository::class)
 * @ORM\Table(name="mock_action_log_entry")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"aeos" = "AeosActionLogEntry", "sms" = "SmsGatewayActionLogEntry"})
 */
abstract class ActionLogEntry
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var array
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="json")
     */
    protected $data;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct($type = null, array $data = null)
    {
        $this->createdAt = new \DateTime();
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return AbstractEntity
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return AbstractEntity
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
}
