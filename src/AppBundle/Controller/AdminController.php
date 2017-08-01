<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Code;
use AppBundle\Service\AeosHelper;
use AppBundle\Service\TemplateManager;
use Gedmo\Blameable\Blameable;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminController extends BaseAdminController
{
    /** @var \AppBundle\Service\TemplateManager */
    protected $templateManager;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    protected $tokenStorage;

    /** @var  AeosHelper */
    protected $aeosHelper;

    public function __construct(TemplateManager $templateManager, TokenStorageInterface $tokenStorage, AeosHelper $aeosHelper)
    {
        $this->templateManager = $templateManager;
        $this->tokenStorage = $tokenStorage;
        $this->aeosHelper = $aeosHelper;
    }

    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $this->limitByUser($dqlFilter, $entityClass, 'entity');
        return parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);
    }

    protected function createSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        // Use only list fields in search query.
        $this->entity['search']['fields'] = array_filter($this->entity['search']['fields'], function ($key) {
            return isset($this->entity['list']['fields'][$key]);
        }, ARRAY_FILTER_USE_KEY);

        $this->limitByUser($dqlFilter, $entityClass, 'entity');
        return parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter);
    }

    private function limitByUser(string &$dqlFilter = null, string $entityClass, string $alias)
    {
        $limitByUserFilter = $this->getLimitByUserFilter($entityClass, $alias);
        if ($limitByUserFilter) {
            if ($dqlFilter) {
                $dqlFilter .= ' and ' . $limitByUserFilter;
            } else {
                $dqlFilter = $limitByUserFilter;
            }
        }
    }

    private function getLimitByUserFilter(string $entityClass, string $alias)
    {
        // instanceof does not work with string as first operand.
        if (!is_subclass_of($entityClass, Blameable::class)) {
            return null;
        }

        /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        if (!$authorizationChecker->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();
            return $alias . '.createdBy = ' . $user->getId();
        }

        return null;
    }

    /**
     * Custom Code form builder to make sure that only some templates are available.
     *
     * @param \AppBundle\Entity\Code $code
     * @param $view
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function createCodeEntityFormBuilder(Code $code, $view)
    {
        $builder = parent::createEntityFormBuilder($code, $view);
        if ($builder->has('template')) {
            $field = $builder->get('template');
            $options = $field->getOptions();
            // The options should include the currently selected template (if any).
            $options['choices'] = $this->templateManager->getUserTemplates($code->getTemplate());
            // We have to unset the "choice_loader" to make "choices" work.
            unset($options['choice_loader']);
            // Replace the "template" field (see https://stackoverflow.com/a/14699235).
            $builder->add($field->getName(), EntityType::class, $options);
        }

        return $builder;
    }

    protected function createNewCodeEntity()
    {
        $code = new Code();

        $code->setStartTime(new \DateTime('today'));
        $code->setEndTime(new \DateTime('today +1 day'));

        return $code;
    }

    protected function prePersistCodeEntity(Code $code)
    {
        $this->createAeosIdentifier($code);
    }

    protected function preUpdateCodeEntity(Code $code)
    {
        if ($code->getIdentifier() === null) {
            $this->createAeosIdentifier($code);
        }
    }

    protected function preRemoveCodeEntity(Code $code)
    {
        if ($code->getIdentifier() !== null) {
            $this->removeAeosIdentifier($code);
        }
    }

    private function createAeosIdentifier(Code $code)
    {
        try {
            $this->aeosHelper->createAeosIdentifier($code);
            $this->showInfo('Code created: %code%', ['%code%' => $code->getIdentifier()]);
        } catch (\Exception $ex) {
            $this->showError($ex->getMessage());
        }
    }

    private function removeAeosIdentifier(Code $code)
    {
        try {
            $this->aeosHelper->deleteAeosIdentifier($code);
            $this->showInfo('Code removed');
        } catch (\Exception $ex) {
            $this->showError($ex->getMessage());
        }
    }

    protected function showInfo(string $message, array $parameters = [])
    {
        $this->showMessage('info', $message, $parameters);
    }

    protected function showError(string $message, array $parameters = [])
    {
        $this->showMessage('error', $message, $parameters);
    }

    protected function showMessage(string $type, string $message, array $parameters = [])
    {
        $translator = $this->get('translator');
        $this->addFlash($type, $translator->trans($message, $parameters));
    }
}
