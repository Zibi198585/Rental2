<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Zdjęcie')
                    ->defaultImageUrl(asset('images/no-image.svg'))
                    ->size(50)
                    ->circular(),
                TextColumn::make('name')
                    ->label('Nazwa produktu')
                    ->searchable(),
                TextColumn::make('price_per_day')
                    ->label('Cena za dzień')
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Ostatnia aktualizacja')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('clone')
                    ->label('Klonuj')
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
                    ->fillForm(function (Model $record) {
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
                    })
                    ->modalHeading('Klonuj produkt')
                    ->modalDescription('Wypełnij formularz dla nowego produktu')
                    ->modalSubmitActionLabel('Utwórz kopię')
                    ->slideOver()
                    ->modalWidth('2xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('clone')
                        ->label('Klonuj wybrane')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $clonedCount = 0;
                            
                            foreach ($records as $record) {
                                $originalName = $record->name;
                                $newName = $originalName . ' (kopia)';
                                
                                // Znajdź unikalną nazwę
                                $counter = 1;
                                while (Product::where('name', $newName)->exists()) {
                                    $counter++;
                                    $newName = $originalName . ' (kopia ' . $counter . ')';
                                }
                                
                                Product::create([
                                    'name' => $newName,
                                    'description' => $record->description,
                                    'price_per_day' => $record->price_per_day,
                                    'image' => $record->image,
                                ]);
                                
                                $clonedCount++;
                            }
                            
                            Notification::make()
                                ->title('Produkty zostały sklonowane')
                                ->body("Utworzono {$clonedCount} " . 
                                    ($clonedCount === 1 ? 'kopię' : 
                                    ($clonedCount < 5 ? 'kopie' : 'kopii')))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Klonuj wybrane produkty')
                        ->modalDescription('Czy na pewno chcesz utworzyć kopie wybranych produktów?')
                        ->modalSubmitActionLabel('Tak, klonuj wszystkie'),
                ]),
            ]);
    }
}
