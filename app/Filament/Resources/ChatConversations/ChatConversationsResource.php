<?php

namespace App\Filament\Resources\ChatConversations;

use App\Filament\Resources\ChatConversations\Pages\ListChatConversations;
use App\Filament\Resources\ChatConversations\Tables\ChatConversationsTable;
use App\Models\ChatConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * LC-03 — the team's view of Smooth-chat conversations: the captured
 * qualification (name, standard, worry, contact) plus the full transcript.
 */
class ChatConversationsResource extends Resource
{
    protected static ?string $model = ChatConversation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Chats';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'chat';

    public static function table(Table $table): Table
    {
        return ChatConversationsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatConversations::route('/'),
        ];
    }
}
