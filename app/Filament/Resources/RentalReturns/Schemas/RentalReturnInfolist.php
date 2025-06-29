<?php

namespace App\Filament\Resources\RentalReturns\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RentalReturnInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('return_number'),
                TextEntry::make('return_date')
                    ->date(),
                TextEntry::make('customer_name'),
                TextEntry::make('customer_phone'),
                TextEntry::make('customer_email'),
                TextEntry::make('transport_cost')
                    ->numeric(),
                IconEntry::make('transport_included')
                    ->boolean(),
                TextEntry::make('pickup_contact_person'),
                TextEntry::make('pickup_contact_phone'),
                TextEntry::make('status'),
                TextEntry::make('returned_by'),
                TextEntry::make('received_by'),
                TextEntry::make('returned_at')
                    ->dateTime(),
                TextEntry::make('equipment_condition'),
                TextEntry::make('damage_fee')
                    ->numeric(),
                TextEntry::make('late_fee')
                    ->numeric(),
                TextEntry::make('additional_fees')
                    ->numeric(),
                TextEntry::make('total_rental_days')
                    ->numeric(),
                TextEntry::make('total_rental_cost')
                    ->numeric(),
                TextEntry::make('total_additional_costs')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
