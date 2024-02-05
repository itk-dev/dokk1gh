<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Admin;

use App\Entity\Setting;
use Doctrine\DBAL\Types\Types;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class SettingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Setting::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE)
            ->disable(Action::NEW);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, new TranslatableMessage('Settings'))
            ->setPageTitle(Crud::PAGE_EDIT, function () {
                /** @var Setting $setting */
                $setting = $this->getContext()->getEntity()->getInstance();

                return new TranslatableMessage('Edit setting {name}', ['name' => new TranslatableMessage($setting->getName())]);
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', new TranslatableMessage('Name'))
            ->formatValue(static fn ($value) => new TranslatableMessage($value))
            ->onlyOnIndex();

        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('value', new TranslatableMessage('Value'));
        } elseif (Crud::PAGE_EDIT === $pageName) {
            /** @var Setting $setting */
            $setting = $this->getContext()->getEntity()->getInstance();
            if (null !== $setting) {
                [$type, $formType] = [$setting->getType(), $setting->getFormType()];
                $fieldType = match ($type) {
                    Types::TEXT => match ($formType) {
                        'texteditor' => TextEditorField::class,
                        'textarea' => TextareaField::class,
                        default => throw new \RuntimeException(sprintf('Unhandled form type: %s', $formType))
                    },
                    Types::STRING => TextField::class,
                    default => throw new \RuntimeException(sprintf('Unhandled data type: %s', $type))
                };
                \assert(is_a($fieldType, FieldInterface::class, true));
                $field = $fieldType::new('value', false);
                \assert(\in_array(FieldTrait::class, class_uses($field), true));
                yield $field
                    ->setHelp($setting->getDescription() ?: '');
            }
        }
    }
}
