<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** CU-01..03 — a parent's message from the public contact page. */
class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'topic', 'message', 'handled'];

    protected function casts(): array
    {
        return [
            'handled' => 'boolean',
        ];
    }
}
