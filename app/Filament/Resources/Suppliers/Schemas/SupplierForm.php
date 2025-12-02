<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название / контактное лицо')
                    ->required(),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel(),
                Textarea::make('address')
                    ->label('Адрес')
                    ->placeholder('Город, улица, склад №…')
                    ->columnSpanFull(),
                TextInput::make('balance')
                    ->label('Долг поставщику')
                    ->helperText('Сумма, которую мы должны этому поставщику (TJS)')
                    ->numeric()
                    ->default(0)
                    ->prefix('SM')
                    ->disabled(),
                Textarea::make('notes')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
