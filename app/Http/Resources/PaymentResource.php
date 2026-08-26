<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $payment = $this->resource;
        $itemName = $payment->music?->title ?? $payment->title ?? $payment->description ?? 'Purchased item';

        return array_merge(parent::toArray($request), [
            'payment_id' => $payment->id,
            'item_id' => $payment->music_id ?? $payment->service_id,
            'item_name' => $itemName,
        ]);
    }
}
