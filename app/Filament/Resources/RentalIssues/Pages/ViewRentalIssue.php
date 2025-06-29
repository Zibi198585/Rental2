<?php

namespace App\Filament\Resources\RentalIssues\Pages;

use App\Filament\Resources\RentalIssues\RentalIssueResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRentalIssue extends ViewRecord
{
    protected static string $resource = RentalIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
