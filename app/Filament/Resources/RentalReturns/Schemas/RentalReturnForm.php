<?php

namespace App\Filament\Resources\RentalReturns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RentalReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('return_number')
                    ->required(),
                DatePicker::make('return_date')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('related_issue_ids'),
                TextInput::make('related_rental_document_ids'),
                TextInput::make('customer_name'),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('customer_email')
                    ->email(),
                TextInput::make('transport_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('transport_included')
                    ->required(),
                Textarea::make('transport_notes')
                    ->columnSpanFull(),
                Textarea::make('pickup_address')
                    ->columnSpanFull(),
                TextInput::make('pickup_contact_person'),
                TextInput::make('pickup_contact_phone')
                    ->tel(),
                Select::make('status')
                    ->options([
            'draft' => 'Draft',
            'returned' => 'Returned',
            'processed' => 'Processed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('draft')
                    ->required(),
                TextInput::make('returned_by'),
                TextInput::make('received_by'),
                DateTimePicker::make('returned_at'),
                Select::make('equipment_condition')
                    ->options([
            'excellent' => 'Excellent',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
            'damaged' => 'Damaged',
        ])
                    ->default('good')
                    ->required(),
                Textarea::make('condition_notes')
                    ->columnSpanFull(),
                TextInput::make('damage_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('late_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('additional_fees')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('fees_description')
                    ->columnSpanFull(),
                TextInput::make('total_rental_days')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_rental_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_additional_costs')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
