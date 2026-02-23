<?php

declare(strict_types=1);

namespace App\Filament\My\Pages;

use Filament\Pages\Page;

class ParentGuide extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Admissions';

    protected static ?string $navigationLabel = 'Panduan Orang Tua';

    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'panduan-orang-tua';

    protected static ?string $title = 'Panduan Orang Tua';

    protected string $view = 'filament.my.pages.parent-guide';
}
