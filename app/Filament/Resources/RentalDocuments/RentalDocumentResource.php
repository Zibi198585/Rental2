<?php

namespace App\Filament\Resources\RentalDocuments;

use App\Filament\Resources\RentalDocuments\Pages\CreateRentalDocument;
use App\Filament\Resources\RentalDocuments\Pages\EditRentalDocument;
use App\Filament\Resources\RentalDocuments\Pages\ListRentalDocuments;
use App\Filament\Resources\RentalDocuments\Pages\ViewRentalDocument;
use App\Filament\Resources\RentalDocuments\Pages\RentalDocumentStatus;
use App\Filament\Resources\RentalDocuments\Schemas\RentalDocumentForm;
use App\Filament\Resources\RentalDocuments\Schemas\RentalDocumentInfolist;
use App\Filament\Resources\RentalDocuments\Tables\RentalDocumentsTable;
use App\Models\RentalDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Illuminate\Database\Eloquent\Builder;

class RentalDocumentResource extends Resource
{
    protected static ?string $model = RentalDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Wypożyczalnia';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Dokument Wynajmu';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Dokumenty Wynajmu';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dokumenty Wynajmu';
    }

    /**
     * Eager loading dla lepszej wydajności
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'products',
                'products.product'
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return RentalDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RentalDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalDocuments::route('/'),
            'create' => CreateRentalDocument::route('/create'),
            'view' => ViewRentalDocument::route('/{record}'),
            'edit' => EditRentalDocument::route('/{record}/edit'),
            'status' => RentalDocumentStatus::route('/status'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Formatuj kwoty w produktach
        if (isset($data['products'])) {
            foreach ($data['products'] as &$product) {
                if (isset($product['price_per_day'])) {
                    $product['price_per_day'] = number_format((float)$product['price_per_day'], 2, ',', '');
                }
                if (isset($product['total_price'])) {
                    $product['total_price'] = number_format((float)$product['total_price'], 2, ',', '');
                }
            }
        }

        // Formatuj inne kwoty
        if (isset($data['delivery_cost'])) {
            $data['delivery_cost'] = number_format((float)$data['delivery_cost'], 2, ',', '');
        }
        if (isset($data['pickup_cost'])) {
            $data['pickup_cost'] = number_format((float)$data['pickup_cost'], 2, ',', '');
        }
        if (isset($data['deposit'])) {
            $data['deposit'] = number_format((float)$data['deposit'], 2, ',', '');
        }

        return $data;
    }
}
