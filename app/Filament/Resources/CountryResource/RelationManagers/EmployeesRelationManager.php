<?php

namespace App\Filament\Resources\CountryResource\RelationManagers;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("Relationships")
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship(name: 'Country', titleAttribute: 'name')
                            ->native(false)
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(function (Set $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                            ->required(),

                        Forms\Components\Select::make('state_id')
                            ->options(fn ($get): Collection => State::query()
                                ->where('country_id', $get('country_id'))->pluck('name', 'id'))
                            ->native(false)
                            ->live()
                            ->searchable()
                            ->afterStateUpdated(fn ($set) => $set('city_id', null))
                            ->required(),

                        Forms\Components\Select::make('city_id')
                            ->options(fn ($get): Collection => City::query()
                                ->where('state_id', $get('state_id'))->pluck('name', 'id'))
                            ->native(false)
                            ->live()
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('department_id')
                            ->relationship(name: 'Department', titleAttribute: 'name')
                            ->native(false)
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(3),
                Section::make("User Biodata")
                    ->description("Enter your username")
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->required()->columnSpanFull()->native(false)->displayFormat('d/m/Y'),
                    ])->columns(3),
                    Section::make("Other Details")
                    ->description("Other emploee details")
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip_code')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('date_hired')
                            ->required()->native(false)->displayFormat('d/m/Y'),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('state.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_hired')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
