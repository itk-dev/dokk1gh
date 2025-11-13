<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as BaseAbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Gedmo\Blameable\Blameable;
use Symfony\Component\Translation\TranslatableMessage;

abstract class AbstractCrudController extends BaseAbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDateFormat('dd-MM-yyyy')
            ->setTimeFormat('HH:mm:ss')
            ->setDateTimeFormat('dd-MM-yyyy HH:mm:ss')
            ->addFormTheme('admin/form/form.html.twig')
            ->overrideTemplate('layout', 'admin/layout.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addWebpackEncoreEntry('easy_admin');
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $this->limitByUser($queryBuilder, $entityDto);

        return $queryBuilder;
    }

    protected function showSuccess(string $message, array $parameters = []): void
    {
        $this->showMessage('success', $message, $parameters);
    }

    protected function showInfo(string|TranslatableMessage $message, array $parameters = []): void
    {
        $this->showMessage('info', $message, $parameters);
    }

    protected function showWarning(string $message, array $parameters = []): void
    {
        $this->showMessage('warning', $message, $parameters);
    }

    protected function showError(string $message, array $parameters = []): void
    {
        $this->showMessage('error', $message, $parameters);
    }

    protected function showMessage(string $type, string|TranslatableMessage $message, array $parameters = []): void
    {
        $this->addFlash($type, $message);
    }

    private function limitByUser(
        QueryBuilder $queryBuilder,
        EntityDto $entityDto,
    ): void {
        // instanceof does not work with string as first operand.
        if (!is_subclass_of($entityDto->getFqcn(), Blameable::class)) {
            return;
        }

        // Non-admin users can only see own entities.
        if (!$this->isGranted('ROLE_ADMIN')) {
            if ($alias = $queryBuilder->getRootAliases()[0] ?? null) {
                $user = $this->getUser();
                \assert(null === $user || $user instanceof User);
                $queryBuilder->where($alias.'.createdBy = :createdBy')
                    ->setParameter('createdBy', $user?->getId() ?? -1);
            }
        }
    }
}
