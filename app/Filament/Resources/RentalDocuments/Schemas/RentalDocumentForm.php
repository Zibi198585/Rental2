<?php

namespace App\Filament\Resources\RentalDocuments\Schemas;

use App\Models\Product;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Repeater\TableColumn;


class RentalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Sekcja podstawowych informacji
                Section::make('Informacje podstawowe')
                    ->description('Podstawowe dane dotyczące umowy najmu')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('agreement_number')
                                    ->label('Numer umowy')
                                    ->placeholder('np. UMO/2024/001')
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->maxLength(255)
                                    ->columnSpan(['md' => 1]),

                                Select::make('status')
                                    ->label('Status umowy')
                                    ->options([
                                        'draft' => 'Wersja robocza',
                                        'rented' => 'Wynajęta',
                                        'partially_returned' => 'Częściowo zwrócona',
                                        'scheduled_return' => 'Zaplanowany zwrot',
                                        'returned' => 'Zwrócona',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->prefixIcon('heroicon-o-clipboard-document-check')
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(['md' => 1]),

                                TextInput::make('city')
                                    ->label('Miasto')
                                    ->default('Wyry')
                                    ->required()
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->maxLength(255)
                                    ->columnSpan(['md' => 1]),
                            ]),

                        TextInput::make('contractor_full_name')
                            ->label('Nazwa kontrahenta')
                            ->required()
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Imię i nazwisko lub nazwa firmy')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // Sekcja adresu zameldowania
                Section::make('Adres zameldowania')
                    ->description('Adres zameldowania kontrahenta')
                    ->icon('heroicon-o-home')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('address_street')
                                    ->label('Ulica')
                                    ->prefixIcon('heroicon-o-map')
                                    ->placeholder('np. ul. Główna')
                                    ->maxLength(255)
                                    ->columnSpan(['sm' => 3, 'md' => 2]),

                                TextInput::make('address_building_number')
                                    ->label('Nr budynku')
                                    ->prefixIcon('heroicon-o-building-office')
                                    ->placeholder('np. 15')
                                    ->maxLength(255)
                                    ->columnSpan(['sm' => 3, 'md' => 1]),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('address_apartment_number')
                                    ->label('Nr mieszkania')
                                    ->prefixIcon('heroicon-o-key')
                                    ->placeholder('np. 5')
                                    ->maxLength(255)
                                    ->columnSpan(['sm' => 2, 'md' => 1]),

                                TextInput::make('address_postal_code')
                                    ->label('Kod pocztowy')
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->placeholder('00-000')
                                    ->mask('99-999')
                                    ->maxLength(6)
                                    ->columnSpan(['sm' => 2, 'md' => 1]),

                                TextInput::make('address_city')
                                    ->label('Miasto')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->placeholder('np. Warszawa')
                                    ->maxLength(255)
                                    ->columnSpan(['sm' => 2, 'md' => 1]),

                                TextInput::make('address_voivodeship')
                                    ->label('Województwo')
                                    ->prefixIcon('heroicon-o-globe-europe-africa')
                                    ->placeholder('np. mazowieckie')
                                    ->maxLength(255)
                                    ->columnSpan(['sm' => 2, 'md' => 1]),
                            ]),

                        TextInput::make('address_country')
                            ->label('Kraj')
                            ->prefixIcon('heroicon-o-flag')
                            ->placeholder('np. Polska')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // Sekcja dokumentów i danych osobowych
                Section::make('Dokumenty i dane osobowe')
                    ->description('Informacje dotyczące dokumentów tożsamości i danych kontaktowych')
                    ->icon('heroicon-o-identification')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('document_type')
                                    ->label('Typ dokumentu')
                                    ->options([
                                        'identity_card' => 'Dowód osobisty',
                                        'passport' => 'Paszport',
                                        'driving_license' => 'Prawo jazdy',
                                        'other' => 'Inny dokument',
                                    ])
                                    ->default('identity_card')
                                    ->required()
                                    ->prefixIcon('heroicon-o-identification')
                                    ->native(false)
                                    ->live()
                                    ->columnSpan(['md' => 1]),

                                TextInput::make('document_number')
                                    ->label('Numer dokumentu')
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->placeholder('np. ABC123456')
                                    ->maxLength(255)
                                    ->columnSpan(['md' => 1]),
                            ]),

                        TextInput::make('other_document')
                            ->label('Inny dokument (opis)')
                            ->prefixIcon('heroicon-o-document')
                            ->placeholder('Opisz jaki dokument')
                            ->maxLength(255)
                            ->visible(fn (Get $get) => $get('document_type') === 'other')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('pesel')
                                    ->label('PESEL')
                                    ->prefixIcon('heroicon-o-identification')
                                    ->placeholder('00000000000')
                                    ->mask('99999999999')
                                    ->length(11)
                                    ->columnSpan(['md' => 1]),

                                TextInput::make('nip')
                                    ->label('NIP')
                                    ->prefixIcon('heroicon-o-building-office-2')
                                    ->placeholder('0000000000')
                                    ->mask('9999999999')
                                    ->length(10)
                                    ->columnSpan(['md' => 1]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_phone')
                                    ->label('Telefon kontaktowy')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->placeholder('+48 000 000 000')
                                    ->tel()
                                    ->maxLength(255)
                                    ->columnSpan(['md' => 1]),

                                TextInput::make('contact_email')
                                    ->label('Email kontaktowy')
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->placeholder('email@example.com')
                                    ->email()
                                    ->maxLength(255)
                                    ->columnSpan(['md' => 1]),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Sekcja wynajmu
                Section::make('Szczegóły wynajmu')
                    ->description('Informacje dotyczące okresu i kosztów wynajmu')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('rental_date')
                                    ->label('Data wynajmu')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->native(false)
                                    ->displayFormat('d.m.Y')
                                    ->default(now()->toDateString())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $rentalDate = $state;
                                        $expectedReturnDate = $get('expected_return_date');
                                        if ($rentalDate && (!$expectedReturnDate || Carbon::parse($expectedReturnDate)->lte(Carbon::parse($rentalDate)))) {
                                            $newReturn = Carbon::parse($rentalDate)->addDay()->toDateString();
                                            $set('expected_return_date', $newReturn);
                                            $rentalDateObj = Carbon::parse($rentalDate)->startOfDay();
                                            $expectedReturnDateObj = Carbon::parse($newReturn)->startOfDay();
                                            $days = (int) $rentalDateObj->diffInDays($expectedReturnDateObj, false);
                                            $set('rental_days', max($days, 1));
                                        } elseif ($rentalDate && $expectedReturnDate) {
                                            $rentalDateObj = Carbon::parse($rentalDate)->startOfDay();
                                            $expectedReturnDateObj = Carbon::parse($expectedReturnDate)->startOfDay();
                                            $days = (int) $rentalDateObj->diffInDays($expectedReturnDateObj, false);
                                            $set('rental_days', max($days, 1));
                                        } else {
                                            $set('rental_days', null);
                                        }
                                    })
                                    ->columnSpan(['sm' => 3, 'md' => 1]),

                                DatePicker::make('expected_return_date')
                                    ->label('Przewidywany zwrot')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->native(false)
                                    ->displayFormat('d.m.Y')
                                    ->minDate(fn (Get $get) => $get('rental_date') ? Carbon::parse($get('rental_date'))->addDay()->toDateString() : now()->addDay()->toDateString())
                                    ->default(now()->addDay()->toDateString())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $rentalDate = $get('rental_date');
                                        $expectedReturnDate = $state;
                                        if ($rentalDate && $expectedReturnDate) {
                                            $rentalDateObj = Carbon::parse($rentalDate)->startOfDay();
                                            $expectedReturnDateObj = Carbon::parse($expectedReturnDate)->startOfDay();
                                            $days = (int) $rentalDateObj->diffInDays($expectedReturnDateObj, false);
                                            $set('rental_days', max($days, 1));
                                        } else {
                                            $set('rental_days', null);
                                        }
                                    })
                                    ->columnSpan(['sm' => 3, 'md' => 1]),

                                TextInput::make('rental_days')
                                    ->label('Liczba dni')
                                    ->prefixIcon('heroicon-o-clock')
                                    ->numeric()
                                    ->suffix('dni')
                                    ->readOnly()
                                    ->default(function (Get $get) {
                                        $rentalDate = $get('rental_date') ?? now()->toDateString();
                                        $expectedReturnDate = $get('expected_return_date') ?? now()->addDay()->toDateString();
                                        $rentalDate = Carbon::parse($rentalDate)->startOfDay();
                                        $expectedReturnDate = Carbon::parse($expectedReturnDate)->startOfDay();
                                        $days = (int) $rentalDate->diffInDays($expectedReturnDate, false);
                                        return max($days, 1);
                                    })
                                    ->live()
                                    ->columnSpan(['sm' => 3, 'md' => 1]),
                            ]),

                        TextInput::make('equipment_location')
                            ->label('Lokalizacja wynajętego sprzętu')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('np. ul. Kopaniny 2, 43-175 Wyry')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // Sekcja dostawy i kosztów
                Section::make('Dostawa i koszty')
                    ->description('Sposób dostawy i związane z tym koszty')
                    ->icon('heroicon-o-truck')
                    ->collapsible()
                    ->schema([
                        Select::make('delivery_method')
                            ->label('Sposób dostawy')
                            ->options([
                                'self_pickup' => 'Odbiór osobisty',
                                'delivery_to_customer' => 'Dostawa do klienta',
                            ])
                            ->default('self_pickup')
                            ->required()
                            ->prefixIcon('heroicon-o-truck')
                            ->native(false)
                            ->live()
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('delivery_cost')
                                    ->label('Koszt dostawy')
                                    ->prefixIcon('heroicon-o-banknotes')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->placeholder('0')
                                    ->helperText('Podaj kwotę w groszach')
                                    //->visible(fn (Forms\Get $get): bool => $get('delivery_method') === 'dostawa_do_klienta')
                                    ->columnSpan(['sm' => 3, 'md' => 1]),

                                TextInput::make('pickup_cost')
                                    ->label('Koszt odbioru')
                                    ->prefixIcon('heroicon-o-banknotes')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->placeholder('0')
                                    ->helperText('Podaj kwotę w groszach')
                                    ->columnSpan(['sm' => 3, 'md' => 1]),

                                TextInput::make('deposit')
                                    ->label('Kaucja')
                                    ->prefixIcon('heroicon-o-shield-check')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->placeholder('0')
                                    ->helperText('Podaj kwotę w groszach')
                                    ->columnSpan(['sm' => 3, 'md' => 1]),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Sekcja produktów w wynajmie
                Section::make('Produkty w wynajmie')
                    ->description('Lista produktów objętych umową')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->schema([
                        Repeater::make('products')
                            ->relationship('products')
                            ->label('Produkty')
                            ->table([
                                TableColumn::make('Nazwa produktu'),
                                TableColumn::make('Ilość'),
                                TableColumn::make('Cena za dobę/szt.'),
                                TableColumn::make('Wartość razem'),
                            ])
                            ->schema([

                                Select::make('product_id')
                                    ->label('Produkt')
                                    ->options(fn () => Product::pluck('name', 'id'))
                                    //->searchable()
                                    ->required()
                                    ->live()
                                    ->columnSpanFull()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);
                                            $set('price_per_day', $product->price_per_day);
                                            // Wylicz total_price od razu po wyborze produktu
                                            $qty = (float) ($get('quantity') ?? 1);
                                            $set('total_price', $qty * (float) $product->price_per_day);
                                        }
                                    })
                                    ->preload(),


                                TextInput::make('quantity')
                                    ->label('Ilość')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $qty = (float) $state;
                                        $price = (float) $get('price_per_day');
                                        $set('total_price', $qty * $price);
                                    }),

                                TextInput::make('price_per_day')
                                    ->label('Cena za dobę/szt.')
                                    ->numeric()
                                    ->step('0.01')
                                    ->inputMode('decimal')
                                    ->suffix('zł')
                                    ->required()
                                    ->readOnly()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $qty = (float) $get('quantity');
                                        $price = (float) $state;
                                        $set('total_price', $qty * $price);
                                    })
                                    ->formatStateUsing(fn ($state) => number_format((float)$state, 2, ',', ' ')),

                                TextInput::make('total_price')
                                    ->label('Wartość razem')
                                    ->numeric()
                                    ->step('0.01')
                                    ->inputMode('decimal')
                                    ->suffix('zł')
                                    ->readOnly()
                                    ->default(0)
                                    ->formatStateUsing(fn ($state) => number_format((float)$state, 2, ',', ' ')),
                            ])
                            ->addActionLabel('Dodaj produkt')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);

    }
}
