<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-link')
                    ->tooltip('Copy Link to Category')
                    ->action(function (Event $record, $livewire) {
                        $categories = $record->categories()->with('links')->get();

                        if ($categories->isEmpty() || $categories->every(fn ($cat) => $cat->links->isEmpty())) {
                            $livewire->js(
                                "new FilamentNotification()
                    .title('No links found for this event!')
                    .warning()
                    .send()"
                            );

                            return;
                        }

                        $text = $categories
                            ->filter(fn ($category) => $category->links->isNotEmpty())
                            ->map(function ($category) {
                                $linkList = $category->links
                                    ->map(fn ($link) => "  • {$link->title}: {$link->url}")
                                    ->implode("\n");

                                return "📁 {$category->name}\n{$linkList}";
                            })
                            ->implode("\n\n");

                        $text = "🔗 {$record->title} — Links\n\n{$text}";

                        $livewire->js(
                            'window.navigator.clipboard.writeText('.json_encode($text).").then(() => {
                new FilamentNotification()
                    .title('Links copied to clipboard!')
                    .success()
                    .send()
            })"
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
