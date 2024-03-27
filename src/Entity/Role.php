<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

enum Role: string
{
    case USER = 'ROLE_USER';
    case GUEST_ADMIN = 'ROLE_GUEST_ADMIN';
    case TEMPLATE_ADMIN = 'ROLE_TEMPLATE_ADMIN';
    case USER_ADMIN = 'ROLE_USER_ADMIN';
    case CONFIG_ADMIN = 'ROLE_CONFIG_ADMIN';
    case ADMIN = 'ROLE_ADMIN';

    public static function asArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_column(self::cases(), 'name')
        );
    }

    public static function values(): array
    {
        return array_keys(static::asArray());
    }
}
