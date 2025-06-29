<?php

namespace App\Filament\Resources\RentalReturns\Pages;

use App\Filament\Resources\RentalReturns\RentalReturnResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalReturn extends EditRecord
{
    protected static string $resource = RentalReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
