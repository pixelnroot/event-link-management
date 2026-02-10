<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', str()->slug($state)))
                    ->required(),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DissociateAction::make(),
                    DeleteAction::make(),
                    Action::make('copy_link')
                        ->label('Copy Link')
                        ->icon('heroicon-o-link')
                        ->tooltip('Copy Link to Category')
                        ->action(function (Category $record, $livewire) {
                            $links = $record->links->pluck('url')->implode("\n");

                            if (empty($links)) {
                                $livewire->js(
                                    "new FilamentNotification()
                        .title('No links found for this category!')
                        .warning()
                        .send()"
                                );

                                return;
                            }

                            $livewire->js(
                                'window.navigator.clipboard.writeText('.json_encode($links).").then(() => {
                new FilamentNotification()
                    .title('Links copied to clipboard!')
                    .success()
                    .send()
            })"
                            );
                        }),
                    Action::make('reset')
                        ->label('Reset Links')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->tooltip('Reset all links associated with this category')
                        ->requiresConfirmation()
                        ->action(function (Category $record, $livewire) {
                            $record->links()->delete();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
