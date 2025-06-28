<?php

namespace App\Filament\Resources\RentalDocuments\Pages;

use App\Filament\Resources\RentalDocuments\RentalDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalDocument extends EditRecord
{
    protected static string $resource = RentalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
