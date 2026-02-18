<?php

namespace App\Filament\School\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class FeaturesOverviewWidget extends Widget
{
    protected string $view = 'filament.school.widgets.dashboard-overview';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function getCategories(): array
    {
        return array_filter([
            $this->tablesCategory(),
            $this->formsCategory(),
            $this->filtersCategory(),
            $this->actionsCategory(),
            $this->infolistsCategory(),
            $this->pageActionsCategory(),
            $this->navigationCategory(),
        ]);
    }

    protected function tablesCategory(): array
    {
        return [
            'name' => 'Tables & Columns',
            'icon' => 'heroicon-o-table-cells',
            'color' => 'blue',
            'features' => [
                ['name' => 'Searchable & sortable', 'description' => 'Full-text search with sortable column headers', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Image columns', 'description' => 'Thumbnails from media library', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Column summarizers', 'description' => 'Footer totals for pricing', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Inline editing', 'description' => 'Edit cells directly in table', 'url' => '', 'resource' => 'Leave Requests'],
                ['name' => 'Table grouping', 'description' => 'Group rows dynamically', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Live polling', 'description' => 'Auto refresh data', 'url' => '', 'resource' => 'Expenses'],
                ['name' => 'Toggleable columns', 'description' => 'Show / hide columns', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Color columns', 'description' => 'Color swatches display', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Column layouts', 'description' => 'Split & stacked layouts', 'url' => '', 'resource' => 'Authors'],
                ['name' => 'Drag-and-drop reordering', 'description' => 'Reorder rows visually', 'url' => '', 'resource' => 'Brands'],
                ['name' => 'Copyable columns', 'description' => 'Click to copy values', 'url' => '', 'resource' => 'Employees'],
            ],
        ];
    }

    protected function filtersCategory(): array
    {
        return [
            'name' => 'Filters',
            'icon' => 'heroicon-o-funnel',
            'color' => 'violet',
            'features' => [
                ['name' => 'Query builder', 'description' => 'Advanced filter rules', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Select filters', 'description' => 'Dropdown based filtering', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Ternary filter', 'description' => 'Three state toggle filters', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Trashed filter', 'description' => 'Soft delete visibility', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Filters above content', 'description' => 'Inline filter bar', 'url' => '', 'resource' => 'Products'],
            ],
        ];
    }

    protected function actionsCategory(): array
    {
        return [
            'name' => 'Table Actions',
            'icon' => 'heroicon-o-bolt',
            'color' => 'amber',
            'features' => [
                ['name' => 'Action groups', 'description' => 'Grouped dropdown actions', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Slide-over modals', 'description' => 'Side panel forms', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Modal forms', 'description' => 'Popup form dialogs', 'url' => '', 'resource' => 'Customers'],
                ['name' => 'Custom icons & colors', 'description' => 'Styled modals', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Confirmations', 'description' => 'Confirm before run', 'url' => '', 'resource' => 'Projects'],
                ['name' => 'External URLs', 'description' => 'Link actions', 'url' => '', 'resource' => 'Brands'],
                ['name' => 'Tooltips', 'description' => 'Hover hints', 'url' => '', 'resource' => 'Brands'],
                ['name' => 'Dynamic states', 'description' => 'Context aware actions', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Lifecycle hooks', 'description' => 'Pre validation hooks', 'url' => '', 'resource' => 'Expenses'],
                ['name' => 'Infolist modals', 'description' => 'Read-only slide overs', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Bulk actions', 'description' => 'Mass updates', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Conditional logic', 'description' => 'State based actions', 'url' => '', 'resource' => 'Orders'],
            ],
        ];
    }

    protected function pageActionsCategory(): array
    {
        return [
            'name' => 'Page & Header Actions',
            'icon' => 'heroicon-o-rectangle-stack',
            'color' => 'rose',
            'features' => [
                ['name' => 'Replicate', 'description' => 'Duplicate record', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Keyboard shortcuts', 'description' => 'Fast publishing', 'url' => '', 'resource' => 'Posts'],
                ['name' => 'Export', 'description' => 'Export data', 'url' => '', 'resource' => 'Authors'],
                ['name' => 'Import', 'description' => 'Bulk import', 'url' => '', 'resource' => 'Categories'],
                ['name' => 'Badge counts', 'description' => 'Dynamic badges', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Workflow status', 'description' => 'Status transitions', 'url' => '', 'resource' => 'Expenses'],
            ],
        ];
    }

    protected function formsCategory(): array
    {
        return [
            'name' => 'Forms',
            'icon' => 'heroicon-o-pencil-square',
            'color' => 'emerald',
            'features' => [
                ['name' => 'Wizard steps', 'description' => 'Multi-step form flow', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Repeaters', 'description' => 'Dynamic rows', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Builder blocks', 'description' => 'Block based forms', 'url' => '', 'resource' => 'Projects'],
                ['name' => 'Rich editor', 'description' => 'WYSIWYG editing', 'url' => '', 'resource' => 'Posts'],
                ['name' => 'Media uploads', 'description' => 'Multiple images', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Color picker', 'description' => 'Choose colors', 'url' => '', 'resource' => 'Departments'],
                ['name' => 'Inline create', 'description' => 'Create in dropdown', 'url' => '', 'resource' => 'Customers'],
                ['name' => 'Conditional fields', 'description' => 'Dynamic form fields', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Tags input', 'description' => 'Multiple tag entry', 'url' => '', 'resource' => 'Posts'],
                ['name' => 'Reactive totals', 'description' => 'Auto calculations', 'url' => '', 'resource' => 'Expenses'],
                ['name' => 'Tabbed forms', 'description' => 'Multi tab layout', 'url' => '', 'resource' => 'Employees'],
            ],
        ];
    }

    protected function infolistsCategory(): array
    {
        return [
            'name' => 'Infolists',
            'icon' => 'heroicon-o-eye',
            'color' => 'cyan',
            'features' => [
                ['name' => 'Rich text display', 'description' => 'Formatted entries', 'url' => '', 'resource' => 'Posts'],
                ['name' => 'Media display', 'description' => 'Image previews', 'url' => '', 'resource' => 'Posts'],
                ['name' => 'Repeatable entries', 'description' => 'Table layouts', 'url' => '', 'resource' => 'Expenses'],
            ],
        ];
    }

    protected function navigationCategory(): array
    {
        return [
            'name' => 'Navigation & Pages',
            'icon' => 'heroicon-o-squares-2x2',
            'color' => 'gray',
            'features' => [
                ['name' => 'Navigation badges', 'description' => 'Live counters', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Sub navigation', 'description' => 'Page tabs', 'url' => '', 'resource' => 'Posts'],
                ['name' => 'Page tabs', 'description' => 'Filtered views', 'url' => '', 'resource' => 'Employees'],
                ['name' => 'Dashboard widgets', 'description' => 'Charts & stats', 'url' => '', 'resource' => 'Shop'],
                ['name' => 'Manage records', 'description' => 'CRUD in one page', 'url' => '', 'resource' => 'Authors'],
                ['name' => 'Relation managers', 'description' => 'Related data panels', 'url' => '', 'resource' => 'Products'],
                ['name' => 'Soft deletes', 'description' => 'Restore & purge', 'url' => '', 'resource' => 'Orders'],
                ['name' => 'Global search', 'description' => 'Quick search', 'url' => '', 'resource' => 'System'],
                ['name' => 'Manage related records', 'description' => 'Child record pages', 'url' => '', 'resource' => 'Posts'],
            ],
        ];
    }
}
