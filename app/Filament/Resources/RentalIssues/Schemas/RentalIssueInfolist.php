<?php

namespace App\Filament\Resources\RentalIssues\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RentalIssueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('issue_number'),
                TextEntry::make('rentalDocument.id')
                    ->numeric(),
                TextEntry::make('issue_date')
                    ->date(),
                TextEntry::make('customer_name'),
                TextEntry::make('customer_phone'),
                TextEntry::make('customer_email'),
                TextEntry::make('transport_cost')
                    ->numeric(),
                IconEntry::make('transport_included')
                    ->boolean(),
                TextEntry::make('delivery_contact_person'),
                TextEntry::make('delivery_contact_phone'),
                TextEntry::make('status'),
                TextEntry::make('issued_by'),
                TextEntry::make('received_by'),
                TextEntry::make('issued_at')
                    ->dateTime(),
                TextEntry::make('total_daily_cost')
                    ->numeric(),
                TextEntry::make('estimated_total_cost')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
