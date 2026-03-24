<?php

namespace Anil\Comments\Enums;

enum UiTheme: string
{
    case Bootstrap4 = 'bootstrap4';
    case Bootstrap5 = 'bootstrap5';
    case Tailwind = 'tailwind';

    /**
     * Whether this theme is Bootstrap-based (needs Paginator::useBootstrap()).
     */
    public function isBootstrap(): bool
    {
        return match ($this) {
            self::Bootstrap4, self::Bootstrap5 => true,
            self::Tailwind => false,
        };
    }

    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Bootstrap4 => 'Bootstrap 4',
            self::Bootstrap5 => 'Bootstrap 5',
            self::Tailwind   => 'Tailwind CSS',
        };
    }
}
