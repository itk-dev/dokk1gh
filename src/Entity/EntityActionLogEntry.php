<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('itkdev_entity_action_log_entry')]
#[ORM\Index(name: 'entity_idx', columns: ['entity_type', 'entity_id'])]
#[ORM\Entity]
class EntityActionLogEntry
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @var \DateTimeInterface
     */
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected $createdAt;

    /**
     * @param string $entityType
     * @param string $entityId
     * @param string $message
     */
    public function __construct(
        #[ORM\Column(name: 'entity_type', type: Types::STRING, length: 255)]
        private $entityType,
        #[ORM\Column(name: 'entity_id', type: Types::STRING, length: 255)]
        private $entityId,
        #[ORM\Column(name: 'message', type: Types::STRING, length: 255)]
        private $message,
        #[ORM\Column(name: 'context', type: Types::JSON, nullable: true)]
        private ?array $context = null
    ) {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

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
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
