<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Forms\Components\Select;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeeResource\RelationManagers;



class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Employee Management';

    //global search
    protected static ?string $recordTitleAttribute = 'first_name';

    //modify search result title
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->last_name;
    }

    //modify globally searched attributes
    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'middle_name', 'country.name'];
    }

    //modify global search results
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Country' => $record->country->name
        ];
    }

    //help search result to be eagerloaded
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['country']);
    }

    //add a badge
    public static function getNavigationBadge(): string
    {
        return static::getModel()::count();
    }

    //modify badge color
    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 5 ? 'info' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                [
                    Section::make("Relationships")
                        ->schema([
                            Forms\Components\Select::make('country_id')
                                ->relationship(name: 'Country', titleAttribute: 'name')
                                ->native(false)
                                ->live()
                                ->searchable()
                                ->afterStateUpdated(function ($set) {
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
                ]
            )->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Employee Country')
                    ->numeric()
                    ->sortable(),
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
            ->filters(
                [
                    SelectFilter::make('Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Filter by department')
                        ->indicator('department'),

                    Filter::make('created_at')
                        ->form([
                            Forms\Components\DatePicker::make('created_from'),
                            Forms\Components\DatePicker::make('created_until'),
                        ])->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['created_from'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                                )
                                ->when(
                                    $data['created_until'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                                );
                        })
                ]
            )
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->successNotificationTitle("Employee Deleted Successfully"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
