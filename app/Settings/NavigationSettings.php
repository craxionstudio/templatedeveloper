<?php

namespace App\Settings;

/**
 * Menu header & drawer mobile.
 */
class NavigationSettings extends PageSettings
{
    public array $header_items;

    public static function group(): string
    {
        return 'navigation';
    }
}
