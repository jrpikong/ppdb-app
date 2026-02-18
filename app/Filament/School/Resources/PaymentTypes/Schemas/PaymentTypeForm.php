<?php
declare(strict_types=1);

namespace App\Filament\School\Resources\PaymentTypes\Schemas;

use App\Models\PaymentType;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Payment Type Details')
                            ->icon('heroicon-o-currency-dollar')
                            ->columns(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('e.g. SAVING_SEAT')
                                    ->helperText('Unique identifier (uppercase, no spaces).')
                                    ->prefixIcon('heroicon-o-hashtag'),

                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Saving Seat Payment')
                                    ->prefixIcon('heroicon-o-tag'),

                                TextInput::make('amount')
                                    ->label('Amount (IDR)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('IDR')
                                    ->placeholder('2500000'),

                                Select::make('payment_stage')
                                    ->label('Payment Stage')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'pre_submission' => '📋 Pre-Submission (before applying)',
                                        'post_acceptance' => '✅ Post-Acceptance (after accepted)',
                                        'enrollment' => '🎓 Enrollment (during enrollment)',
                                        'installment' => '💳 Installment',
                                    ])
                                    ->helperText('When is this payment required?'),
                            ]),

                        Section::make('Bank & Instructions')
                            ->icon('heroicon-o-building-library')
                            ->collapsed(false)
                            ->columns(2)
                            ->schema([
                                \Filament\Forms\Components\KeyValue::make('bank_info')
                                    ->label('Bank Account Info')
                                    ->helperText('Bank name, account number, holder name, etc.')
                                    ->columnSpanFull(),

                                RichEditor::make('payment_instructions')
                                    ->label('Payment Instructions')
                                    ->placeholder('Step-by-step instructions for parents...')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?PaymentType $record) => $record === null ? 3 : 2]),

                // ✅ SIDEBAR - Info metadata (hanya muncul saat edit)
                Section::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_mandatory')
                            ->label('Mandatory')
                            ->helperText('Must be paid to proceed.')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('is_refundable')
                            ->label('Refundable')
                            ->helperText('Can be refunded if withdrawn.')
                            ->default(false)
                            ->inline(false),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive types won\'t appear.')
                            ->default(true)
                            ->inline(false),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->columnSpanFull()
                            ->hidden(fn(?PaymentType $record) => $record === null)
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->columnSpanFull()
                            ->hidden(fn(?PaymentType $record) => $record === null)
                            ->dateTime('d M Y, H:i'),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
