<?php

namespace App\Filament\Resources\RentalReturns\Pages;

use App\Filament\Resources\RentalReturns\RentalReturnResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRentalReturn extends ViewRecord
{
    protected static string $resource = RentalReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
