<?php

namespace App\Filament\Resources\RentalReturns;

use App\Filament\Resources\RentalReturns\Pages\CreateRentalReturn;
use App\Filament\Resources\RentalReturns\Pages\EditRentalReturn;
use App\Filament\Resources\RentalReturns\Pages\ListRentalReturns;
use App\Filament\Resources\RentalReturns\Pages\ViewRentalReturn;
use App\Filament\Resources\RentalReturns\Schemas\RentalReturnForm;
use App\Filament\Resources\RentalReturns\Schemas\RentalReturnInfolist;
use App\Filament\Resources\RentalReturns\Tables\RentalReturnsTable;
use App\Models\RentalReturn;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalReturnResource extends Resource
{
    protected static ?string $model = RentalReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownTray;

    protected static string|UnitEnum|null $navigationGroup = 'Wydania i Zwroty';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Zwrot';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Zwroty';
    }

    public static function getNavigationLabel(): string
    {
        return 'Zwroty Sprzętu';
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
        return RentalReturnForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RentalReturnInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalReturnsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalReturns::route('/'),
            'create' => CreateRentalReturn::route('/create'),
            'view' => ViewRentalReturn::route('/{record}'),
            'edit' => EditRentalReturn::route('/{record}/edit'),
        ];
    }
}
