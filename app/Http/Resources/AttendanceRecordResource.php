<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $totalBreakMinutes = $this->whenLoaded('breaks')
            ? $this->breaks->sum(function ($break) {

                if (! $break->break_in || ! $break->break_out) {
                    return 0;
                }

                return Carbon::parse($break->break_out)
                    ->diffInMinutes(
                        Carbon::parse($break->break_in)
                    );
            })
            : 0;

        $totalBreakTime = sprintf(
            '%02d:%02d',
            floor($totalBreakMinutes / 60),
            $totalBreakMinutes % 60
        );

        $totalTime = null;

        if ($this->clock_in && $this->clock_out) {

            $workMinutes =
                Carbon::parse($this->clock_out)
                    ->diffInMinutes(
                        Carbon::parse($this->clock_in)
                    ) - $totalBreakMinutes;

            $totalTime = sprintf(
                '%02d:%02d',
                floor($workMinutes / 60),
                $workMinutes % 60
            );
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'date' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,

            'total_time' => $totalTime,
            'total_break_time' => $totalBreakTime,

            'comment' => $this->comment,

            'breaks' => AttendanceBreakResource::collection(
                $this->whenLoaded('breaks')
            ),

            'applications' => ApplicationResource::collection(
                $this->whenLoaded('applications')
            ),
        ];
    }
}
