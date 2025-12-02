<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Models\Client as ClientModel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя / Компания')
                    ->required(),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->placeholder('+992 900 00 00 00'),
                Textarea::make('address')
                    ->label('Адрес')
                    ->placeholder('Город, улица, ориентир')
                    ->columnSpanFull(),
                TextInput::make('balance')
                    ->label('Текущий долг клиента')
                    ->numeric()
                    ->default(0)
                    ->prefix('SM')
                    ->helperText('Можно заполнить только при создании клиента.')
                    ->disabled(fn(?ClientModel $record) => filled($record))
                    ->dehydrated(fn(?ClientModel $record) => blank($record)),
                TextInput::make('lifetime_spend')
                    ->label('Сумма покупок за всё время')
                    ->numeric()
                    ->prefix('SM')
                    ->disabled(),
                Textarea::make('notes')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
