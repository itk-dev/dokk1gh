<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\EntityActionLogEntry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;

class EntityActionLogger
{
    public function __construct(
        private readonly EntityManagerInterface $manager
    ) {
    }

    public function log(object $entity, string $message, ?array $context = null): void
    {
        [$entityType, $entityId] = $this->getEntityTypeAndId($entity);
        $entry = new EntityActionLogEntry($entityType, $entityId, $message, $context);
        $this->manager->persist($entry);
        $this->manager->flush();
    }

    public function getActionLogEntries(object $entity, array $criteria = [], ?array $orderBy = null): array
    {
        [$entityType, $entityId] = $this->getEntityTypeAndId($entity);
        $criteria += [
            'entityType' => $entityType,
            'entityId' => $entityId,
        ];
        if (null === $orderBy) {
            $orderBy = ['createdAt' => Criteria::DESC];
        }

        return $this->manager->getRepository(EntityActionLogEntry::class)->findBy($criteria, $orderBy);
    }

    private function getEntityTypeAndId(object $entity): array
    {
        $entityType = $entity::class;

        if (method_exists($entity, 'getId')) {
            $entityId = $entity->getId();
        } elseif (method_exists($entity, 'id')) {
            $entityId = $entity->id();
        } elseif (isset($entity->id)) {
            $entityId = $entity->id;
        } else {
            throw new \RuntimeException('Cannot get entity id');
        }

        return [$entityType, $entityId];
    }
}
