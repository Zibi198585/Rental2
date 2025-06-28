<?php

namespace App\Filament\Resources\RentalDocuments;

use App\Filament\Resources\RentalDocuments\Pages\CreateRentalDocument;
use App\Filament\Resources\RentalDocuments\Pages\EditRentalDocument;
use App\Filament\Resources\RentalDocuments\Pages\ListRentalDocuments;
use App\Filament\Resources\RentalDocuments\Pages\ViewRentalDocument;
use App\Filament\Resources\RentalDocuments\Schemas\RentalDocumentForm;
use App\Filament\Resources\RentalDocuments\Schemas\RentalDocumentInfolist;
use App\Filament\Resources\RentalDocuments\Tables\RentalDocumentsTable;
use App\Models\RentalDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RentalDocumentResource extends Resource
{
    protected static ?string $model = RentalDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;


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

    public static function getNavigationGroup(): ?string
    {
        return 'Wypożyczalnia';
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentalDocuments::route('/'),
            'create' => CreateRentalDocument::route('/create'),
            'view' => ViewRentalDocument::route('/{record}'),
            'edit' => EditRentalDocument::route('/{record}/edit'),
        ];
    }
}
