<?php

namespace AppBundle\Filter;

use AppBundle\Entity\Template;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use PDO;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserTemplateFilter extends SQLFilter
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->reflClass->name !== Template::class) {
            return '';
        }

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return '';
        }

        // Limit templates to the ones added to the user.
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // Using User::getTemplates will create an infinite recursion.
        $sql = 'select template_id from user_template where user_id = :user_id';
        $stmt = $this->container->get('doctrine')->getManager()->getConnection()->prepare($sql);
        $stmt->execute(['user_id' => $user->getId()]);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $targetTableAlias . '.id in (-1, ' . implode(', ', $result) . ')';
    }
}
