<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A child (student) as the parent app lists them: id, name, and a plain SEA standard label.
 *
 * @mixin User
 */
class ChildSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'standard' => $this->seaStandardLabel(),
        ];
    }
}
