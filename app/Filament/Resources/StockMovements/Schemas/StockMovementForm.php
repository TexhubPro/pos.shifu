<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Товар')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('referenceable_type')
                    ->label('Связанный документ (тип)')
                    ->helperText('Например: Purchase или Sale'),
                TextInput::make('referenceable_id')
                    ->label('ID документа')
                    ->numeric(),
                Select::make('type')
                    ->label('Тип движения')
                    ->options([
                        'purchase' => 'Поступление',
                        'sale' => 'Продажа',
                        'correction' => 'Коррекция',
                        'adjustment' => 'Корректировка',
                        'return' => 'Возврат',
                    ])
                    ->required(),
                TextInput::make('quantity')
                    ->label('Количество')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_after')
                    ->label('Остаток после')
                    ->numeric(),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->columnSpanFull(),
                DateTimePicker::make('occurred_at')
                    ->label('Дата движения')
                    ->seconds(false)
                    ->required(),
                Textarea::make('meta')
                    ->label('Доп. данные')
                    ->columnSpanFull(),
            ]);
    }
}
