<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название бренда')
                    ->required(),
                TextInput::make('slug')
                    ->label('ЧПУ (slug)')
                    ->disabled()
                    ->hint('Генерируется автоматически'),
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
            ]);
    }
}
