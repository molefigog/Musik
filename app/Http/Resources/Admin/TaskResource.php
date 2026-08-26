<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Admin sees everything, including raw paths and the customer's info.
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'service_type' => $this->service_type,
            'title' => $this->title,
            'details' => $this->details,
            'amount' => $this->amount,
            'is_paid' => $this->is_paid,
            'status' => $this->status,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'preview_path' => $this->preview_path,
            'preview_url' => $this->preview_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
