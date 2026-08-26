<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// What the customer sees. No raw storage paths, just usable URLs
// gated behind status so the frontend can't accidentally leak an
// unfinished file's location.
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_type' => $this->service_type,
            'title' => $this->title,
            'details' => $this->details,
            'amount' => $this->amount,
            'is_paid' => $this->is_paid,
            'status' => $this->status,
            'file_url' => $this->status ? $this->file_url : null,
            'preview_url' => $this->status ? $this->preview_url : null,
            'created_at' => $this->created_at,
        ];
    }
}
