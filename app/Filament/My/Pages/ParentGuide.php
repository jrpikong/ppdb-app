<?php

declare(strict_types=1);

namespace App\Filament\My\Pages;

use Filament\Pages\Page;

class ParentGuide extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = "Parents' Guide";

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'parent-guide';

    protected static ?string $title = "Parents' Guide";

    protected string $view = 'filament.my.pages.parent-guide';
}
