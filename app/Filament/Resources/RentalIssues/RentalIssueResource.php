<?php

namespace App\Filament\Resources\RentalIssues;

use App\Filament\Resources\RentalIssues\Pages\CreateRentalIssue;
use App\Filament\Resources\RentalIssues\Pages\EditRentalIssue;
use App\Filament\Resources\RentalIssues\Pages\ListRentalIssues;
use App\Filament\Resources\RentalIssues\Pages\ViewRentalIssue;
use App\Filament\Resources\RentalIssues\Schemas\RentalIssueForm;
use App\Filament\Resources\RentalIssues\Schemas\RentalIssueInfolist;
use App\Filament\Resources\RentalIssues\Tables\RentalIssuesTable;
use App\Models\RentalIssue;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RentalIssueResource extends Resource
{
    protected static ?string $model = RentalIssue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Wydania i Zwroty';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Wydanie';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Wydania';
    }

    public static function getNavigationLabel(): string
    {
        return 'Wydania Sprzętu';
    }

    /**
     * Eager loading dla lepszej wydajności
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'products',
                'products.product',
                'rentalDocument'
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return RentalIssueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RentalIssueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalIssuesTable::configure($table);
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
            'index' => ListRentalIssues::route('/'),
            'create' => CreateRentalIssue::route('/create'),
            'view' => ViewRentalIssue::route('/{record}'),
            'edit' => EditRentalIssue::route('/{record}/edit'),
        ];
    }
}
