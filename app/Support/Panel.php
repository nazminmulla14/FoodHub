<?php

namespace App\Support;

class Panel
{
    public static function routePrefix(): string
    {
        return request()->routeIs('owner.*') ? 'owner.' : 'admin.';
    }

    public static function route(string $name, mixed ...$parameters): string
    {
        return route(self::routePrefix().$name, $parameters);
    }
}
