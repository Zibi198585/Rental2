<?php

namespace App\Filament\Resources\RentalDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class RentalDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Header - Horizontal Layout
                Section::make()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('agreement_number')
                                    ->label('Numer umowy')
                                    ->icon('heroicon-o-document-text')
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('—'),

                                TextEntry::make('contractor_full_name')
                                    ->label('Kontrahent')
                                    ->icon('heroicon-o-user')
                                    ->weight(FontWeight::SemiBold)
                                    ->copyable()
                                    ->color('gray')
                                    ->wrap(),

                                TextEntry::make('rental_date')
                                    ->label('Data najmu')
                                    ->date('d.m.Y')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('—'),

                                TextEntry::make('deposit')
                                    ->label('Kaucja')
                                    ->money('PLN')
                                    ->icon('heroicon-o-banknotes')
                                    ->weight(FontWeight::Bold)
                                    ->color('danger')
                                    ->placeholder('0,00 zł'),
                            ]),
                    ])
                    ->compact(),

                // Three Equal Columns Layout
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        // Column 1: Personal Data
                        Section::make('Dane osobowe')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                TextEntry::make('document_type')
                                    ->label('Dokument')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'identity_card' => 'Dowód osobisty',
                                        'passport' => 'Paszport',
                                        'driving_license' => 'Prawo jazdy',
                                        'other' => 'Inny',
                                        default => '—',
                                    })
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'identity_card' => 'primary',
                                        'passport' => 'success',
                                        'driving_license' => 'warning',
                                        'other' => 'gray',
                                        default => 'gray',
                                    }),

                                TextEntry::make('document_number')
                                    ->label('Numer')
                                    ->copyable()
                                    ->fontFamily('mono')
                                    ->placeholder('—'),

                                TextEntry::make('other_document')
                                    ->label('Opis dokumentu')
                                    ->visible(fn ($record) => $record?->document_type === 'other')
                                    //->italic()
                                    ->placeholder('—'),

                                TextEntry::make('pesel')
                                    ->label('PESEL')
                                    ->copyable()
                                    ->fontFamily('mono')
                                    ->placeholder('—'),

                                TextEntry::make('nip')
                                    ->label('NIP')
                                    ->copyable()
                                    ->fontFamily('mono')
                                    ->placeholder('—'),
                            ]),

                        // Column 2: Contact
                        Section::make('Kontakt')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextEntry::make('contact_phone')
                                    ->label('Telefon')
                                    ->icon('heroicon-o-phone')
                                    ->copyable()
                                    ->url(fn (?string $state) => $state ? "tel:{$state}" : null)
                                    ->color('success')
                                    ->weight(FontWeight::Medium)
                                    ->placeholder('—'),

                                TextEntry::make('contact_email')
                                    ->label('E-mail')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->url(fn (?string $state) => $state ? "mailto:{$state}" : null)
                                    ->color('info')
                                    ->weight(FontWeight::Medium)
                                    ->placeholder('—'),

                                TextEntry::make('city')
                                    ->label('Miasto najmu')
                                    ->icon('heroicon-o-building-storefront')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('equipment_location')
                                    ->label('Lokalizacja sprzętu')
                                    ->icon('heroicon-o-map-pin')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('—'),
                            ]),

                        // Column 3: Rental Details
                        Section::make('Szczegóły najmu')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                TextEntry::make('expected_return_date')
                                    ->label('Planowany zwrot')
                                    ->date('d.m.Y')
                                    ->icon('heroicon-o-arrow-uturn-left')
                                    ->badge()
                                    ->color('warning')
                                    ->placeholder('—'),

                                TextEntry::make('rental_days')
                                    ->label('Liczba dni')
                                    ->numeric()
                                    ->suffix(' dni')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('—'),

                                TextEntry::make('delivery_method')
                                    ->label('Dostawa')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'self_pickup' => 'Odbiór własny',
                                        'delivery_to_customer' => 'Dostawa',
                                        default => '—',
                                    })
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'self_pickup' => 'success',
                                        'delivery_to_customer' => 'warning',
                                        default => 'gray',
                                    }),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('delivery_cost')
                                            ->label('Koszt dostawy')
                                            ->money('PLN')
                                            ->color('warning')
                                            ->placeholder('0,00 zł'),

                                        TextEntry::make('pickup_cost')
                                            ->label('Koszt odbioru')
                                            ->money('PLN')
                                            ->color('warning')
                                            ->placeholder('0,00 zł'),
                                    ]),
                            ]),
                    ]),

                // Address Section - Full Width
                Section::make('Adres zamieszkania')
                    ->icon('heroicon-o-home-modern')
                    ->collapsible()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 6])
                            ->schema([
                                TextEntry::make('address_street')
                                    ->label('Ulica')
                                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2])
                                    ->placeholder('—'),

                                TextEntry::make('address_building_number')
                                    ->label('Nr budynku')
                                    ->columnSpan(1)
                                    ->placeholder('—'),

                                TextEntry::make('address_apartment_number')
                                    ->label('Nr mieszkania')
                                    ->columnSpan(1)
                                    ->placeholder('—'),

                                TextEntry::make('address_postal_code')
                                    ->label('Kod pocztowy')
                                    ->fontFamily('mono')
                                    ->columnSpan(1)
                                    ->placeholder('—'),

                                TextEntry::make('address_city')
                                    ->label('Miasto')
                                    ->columnSpan(1)
                                    ->placeholder('—'),
                            ]),

                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextEntry::make('address_voivodeship')
                                    ->label('Województwo')
                                    ->icon('heroicon-o-globe-europe-africa')
                                    ->placeholder('—'),

                                TextEntry::make('address_country')
                                    ->label('Kraj')
                                    ->icon('heroicon-o-flag')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('—'),
                            ]),
                    ]),

                // System Info - Collapsed
                Section::make('Informacje systemowe')
                    ->icon('heroicon-o-server-stack')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Utworzono')
                                    ->dateTime('d.m.Y H:i')
                                    ->icon('heroicon-o-plus-circle')
                                    ->color('success'),

                                TextEntry::make('updated_at')
                                    ->label('Zaktualizowano')
                                    ->dateTime('d.m.Y H:i')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('info'),
                            ]),
                    ]),
            ]);
    }
}
