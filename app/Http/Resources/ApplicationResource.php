<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'requested_clock_in' => $this->requested_clock_in,
            'requested_clock_out' => $this->requested_clock_out,
            'comment' => $this->comment,
        ];
    }
}
