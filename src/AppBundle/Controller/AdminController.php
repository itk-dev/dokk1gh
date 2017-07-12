<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Code;
use AppBundle\Entity\User;
use AppBundle\Service\TemplateManager;
use Gedmo\Blameable\Blameable;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AdminController extends BaseAdminController
{
    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
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

        /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $token */
        $token = $this->get('security.token_storage')->getToken();
        /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker */
        $authorizationChecker = $this->get('security.authorization_checker');
        if (!$authorizationChecker->isGranted('ROLE_ADMIN')) {
            return $alias . '.createdBy = ' . $token->getUser()->getId();
        }

        return null;
    }

    // @see http://symfony.com/doc/current/bundles/EasyAdminBundle/integration/fosuserbundle.html
    public function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    public function prePersistUserEntity(User $user)
    {
        $user->setUsername($user->getEmail());
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }

    public function preUpdateUserEntity(User $user)
    {
        $user->setUsername($user->getEmail());
        $this->get('fos_user.user_manager')->updateUser($user, false);
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
}
