<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название категории')
                    ->placeholder('Например: Обувь')
                    ->required(),
                TextInput::make('slug')
                    ->label('ЧПУ (slug)')
                    ->disabled()
                    ->helperText('Формируется автоматически из названия'),
                Select::make('parent_id')
                    ->label('Родительская категория')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->placeholder('Без родителя'),
                Textarea::make('description')
                    ->label('Описание')
                    ->placeholder('Дополнительная информация о категории')
                    ->columnSpanFull(),
            ]);
    }
}
