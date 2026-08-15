<?php

namespace App\Filament\Resources\ChatConversations\Pages;

use App\Filament\Resources\ChatConversations\ChatConversationsResource;
use Filament\Resources\Pages\ListRecords;

class ListChatConversations extends ListRecords
{
    protected static string $resource = ChatConversationsResource::class;
}
