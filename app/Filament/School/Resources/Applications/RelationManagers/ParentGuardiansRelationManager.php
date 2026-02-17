<?php

declare(strict_types=1);

namespace App\Filament\School\Resources\Applications\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ParentGuardiansRelationManager extends RelationManager
{
    protected static string $relationship = 'parentGuardians';

    protected static ?string $title = 'Parent/Guardian Information';

    protected static string|null|\BackedEnum $icon = 'heroicon-o-users';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Parent/Guardian Details')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('relationship')
                            ->label('Relationship')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Legal Guardian',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(1),

                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20)
                            ->columnSpan(1),

                        TextInput::make('occupation')
                            ->label('Occupation')
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('workplace')
                            ->label('Workplace')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(2)
                            ->columnSpanFull(),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('country')
                            ->label('Country')
                            ->default('Indonesia')
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(20)
                            ->columnSpan(1),

                        Toggle::make('is_primary_contact')
                            ->label('Primary Contact')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),

                        Toggle::make('is_emergency_contact')
                            ->label('Emergency Contact')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),

                        Toggle::make('lives_with_student')
                            ->label('Lives with Student')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('relationship')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'father' => 'blue',
                        'mother' => 'pink',
                        'guardian' => 'purple',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('phone')
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('occupation')
                    ->placeholder('N/A')
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_primary_contact')
                    ->label('Primary')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_emergency_contact')
                    ->label('Emergency')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('relationship')
                    ->options([
                        'father' => 'Father',
                        'mother' => 'Mother',
                        'guardian' => 'Guardian',
                        'other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_primary_contact')
                    ->label('Primary Contact'),
                Tables\Filters\TernaryFilter::make('is_emergency_contact')
                    ->label('Emergency Contact'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No parent/guardian information yet')
            ->emptyStateDescription('Add parent or guardian details for this application.')
            ->emptyStateIcon('heroicon-o-users');
    }
}
