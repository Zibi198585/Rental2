<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Product;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('clone')
                ->label('Klonuj produkt')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->form([
                    TextInput::make('name')
                        ->label('Nazwa produktu')
                        ->required()
                        ->maxLength(255)
                        ->unique(Product::class, 'name'),
                        
                    FileUpload::make('image')
                        ->label('Zdjęcie produktu')
                        ->image()
                        ->directory('products')
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                        
                    Textarea::make('description')
                        ->label('Opis produktu')
                        ->rows(3),
                        
                    TextInput::make('price_per_day')
                        ->label('Cena za dzień (PLN)')
                        ->numeric()
                        ->required()
                        ->minValue(0),
                ])
                ->fillForm(function () {
                    $record = $this->record;
                    $originalName = $record->name;
                    $newName = $originalName . ' (kopia)';
                    
                    // Znajdź unikalną nazwę
                    $counter = 1;
                    while (Product::where('name', $newName)->exists()) {
                        $counter++;
                        $newName = $originalName . ' (kopia ' . $counter . ')';
                    }
                    
                    return [
                        'name' => $newName,
                        'description' => $record->description,
                        'price_per_day' => $record->price_per_day,
                        'image' => $record->image,
                    ];
                })
                ->action(function (array $data) {
                    $product = Product::create($data);
                    
                    Notification::make()
                        ->title('Produkt został sklonowany')
                        ->body("Utworzono: {$data['name']}")
                        ->success()
                        ->send();
                        
                    return redirect()->to(ProductResource::getUrl('edit', ['record' => $product]));
                })
                ->modalHeading('Klonuj produkt')
                ->modalDescription('Wypełnij formularz dla nowego produktu')
                ->modalSubmitActionLabel('Utwórz kopię')
                ->slideOver()
                ->modalWidth('2xl'),
        ];
    }
}
