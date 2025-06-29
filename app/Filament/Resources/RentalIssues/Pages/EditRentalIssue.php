<?php

namespace App\Filament\Resources\RentalIssues\Pages;

use App\Filament\Resources\RentalIssues\RentalIssueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRentalIssue extends EditRecord
{
    protected static string $resource = RentalIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
