<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\EmployeeResource\Pages;
use App\Filament\App\Resources\EmployeeResource\RelationManagers;
use Illuminate\Support\Collection;
use Filament\Facades\Filament;
use App\Models\Employee;
use App\Models\State;
use App\Models\City;
use App\Models\Department;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup="Employee Management";
    protected static ?string $recordTitleAttribute='first_name';
    public static function getGlobalSearchResultTitle(Model $record):string{
        return $record->last_name;
    }
    public static function  getGloballySearchableAttributes():array{
        return ['first_name','last_name','middle_name','country.name'];
    }
    public static function getGlobalSearchResultDetails(Model $record): array{
        return [
         'Country'=>$record->country->name,
        ];
    }
     public static function getGlobalSearchEloquentQuery():Builder{
        return parent::getGlobalSearchEloquentQuery()->with(['country']);
     }
     public static function  getNavigationBadge(): ?string{
       return static::getModel()::count(); 
     }
     public static function getNavigationBadgeColor(): string|array|null{
        return static::getModel()::count() > 5 ?'warning':'success'; 
     } 
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('User Location')
              ->description('Put the Location details here in')
              ->schema([
                Forms\Components\Select::make('country_id')
            ->relationship(name:'country',titleAttribute:'name')
            ->searchable()
            ->preload()
            ->live()
            ->afterStateUpdated(function (Set $set){
                $set('state_id',null);
                $set('city_id',null);
            } )
            ->required(),
            Forms\Components\Select::make('state_id')
            ->options(fn(Get $get): Collection=>State::query()
            ->where('country_id',$get('country_id'))
            ->pluck('name','id'))
            ->searchable()
            ->preload()
            ->live()
            ->afterStateUpdated(fn (Set $set)=> $set('city_id',null))
            ->required(),
            Forms\Components\Select::make('city_id')
            ->options(fn(Get $get): Collection=>City::query()
            ->where('state_id',$get('state_id'))
            ->pluck('name','id'))
            ->searchable()
            ->preload()
            ->live()
            ->afterStateUpdated(fn (Set $set)=> $set('department_id',null))
            ->required(),
            Forms\Components\Select::make('department_id')
            ->relationship(name:'department',titleAttribute:'name',
            modifyQueryUsing:fn(Builder $query)=>$query->whereBelongsTo(Filament::getTenant()))
            ->searchable()
            ->preload()
            ->live()
           
            ->required(),
              ])->columns(2),
            Forms\Components\TextInput::make('first_name')
                ->required(),
            Forms\Components\TextInput::make('last_name')
                ->required(),
            Forms\Components\TextInput::make('middle_name')
                ->required(),
            Forms\Components\TextInput::make('address')
                ->required(),
            Forms\Components\TextInput::make('zip_code')
                ->required(),
                Forms\Components\Section::make('Dates')
                ->description('Pick dates here in')
                ->schema([  
            Forms\Components\DatePicker::make('date_of_birth')
            ->native(false)
                ->required(),
            Forms\Components\DatePicker::make('date_hired')
                ->required()
               ,
               ])->columns(2),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('country.name')
                //->numeric()
                ->sortable()
               ,
            Tables\Columns\TextColumn::make('state.name')
                //->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('city.name')
                //->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('department.name')
               // ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('first_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('last_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('middle_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('address')
                ->searchable(),
            Tables\Columns\TextColumn::make('zip_code')
                ->searchable(),
            Tables\Columns\TextColumn::make('date_of_birth')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
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
            SelectFilter::make('Department')
            ->relationship('department','name')
            ->preload()
            ->searchable()
            ->label('Filter By Department')
            ->indicator('Department'),
            Filter::make('created_at')
            ->form([
                DatePicker::make('created_from'),
                DatePicker::make('created_until'),
            ])
            ->query(function (Builder $query, array $data): Builder {
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
            ->columnSpan(2)->columns(2),
        
        ], layout:FiltersLayout::AboveContent)->filtersFormColumns(3)
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()
               ->successNotification(Notification::make()
               ->success()
               ->title('Employee deleted.')
               ->body('The Employee deleted successfully.'))
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
