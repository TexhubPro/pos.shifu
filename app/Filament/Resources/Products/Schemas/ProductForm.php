<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        Select::make('brand_id')
                            ->label('Бренд')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('category_id')
                            ->label('Категория')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Grid::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('barcode')
                                    ->label('Штрихкод')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ])
                            ->columnSpan(2),
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(4)
                            ->columnSpan(2),
                    ]),
                Section::make('Цены и остатки')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_price')
                            ->label('Себестоимость (TJS)')
                            ->required()
                            ->numeric()
                            ->prefix('SM'),
                        TextInput::make('sale_price')
                            ->label('Цена продажи (TJS)')
                            ->required()
                            ->numeric()
                            ->prefix('SM'),
                        TextInput::make('wholesale_price')
                            ->label('Оптовая цена (TJS)')
                            ->numeric()
                            ->prefix('SM'),
                        TextInput::make('stock')
                            ->label('Количество на складе')
                            ->numeric()
                            ->default(0),
                        TextInput::make('low_stock_threshold')
                            ->label('Минимальный остаток')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ]),
                Section::make('Медиа')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('images')
                            ->label('Изображения')
                            ->image()
                            ->imageEditor()
                            ->multiple()
                            ->directory('products/images')
                            ->reorderable()
                            ->hint('Можно загрузить до 5 изображений')
                            ->maxFiles(5),
                    ]),
            ]);
    }
}
