<?php

namespace App\Models;

use Database\Factories\CoParentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoParent extends Model
{
    /** @use HasFactory<CoParentFactory> */
    use HasFactory;

    protected $fillable = [
        'guardian_id',
        'name',
        'email',
        'relationship',
        'status',
        'invited_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
        ];
    }

    /**
     * The primary guardian who invited this co-parent.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }
}
