<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;

class AttendanceRecordController extends Controller
{
    /**
     * 公開API：勤怠一覧取得 (GET)
     * FN058
     */
    public function index(
        IndexAttendanceRecordRequest $request
    ) {
        $perPage = min(
            $request->input('per_page', 20),
            100
        );

        $records = AttendanceRecord::with([
            'user',
            'breaks',
        ])
            ->when(
                $request->user_id,
                fn ($q) => $q->where(
                    'user_id',
                    $request->user_id
                )
            )
            ->when(
                $request->date,
                fn ($q) => $q->whereDate(
                    'date',
                    $request->date
                )
            )
            ->latest('date')
            ->paginate($perPage);

        return AttendanceRecordResource::collection(
            $records
        );
    }

    /**
     * 公開API：勤怠詳細取得 (GET)
     * FN059
     */
    public function show(
        AttendanceRecord $attendanceRecord
    ) {
        $attendanceRecord->load([
            'user',
            'breaks',
            'applications',
        ]);

        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    public function store(
        StoreAttendanceRecordRequest $request
    ) {
        $attendanceRecord =
            $request->user()
                ->attendanceRecords()
                ->create(
                    $request->validated()
                );

        $attendanceRecord->load([
            'user',
            'breaks',
        ]);

        return (
            new AttendanceRecordResource(
                $attendanceRecord
            )
        )
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateAttendanceRecordRequest $request,
        AttendanceRecord $attendanceRecord
    ) {
        $this->authorize(
            'update',
            $attendanceRecord
        );

        $attendanceRecord->update(
            $request->validated()
        );

        return new AttendanceRecordResource(
            $attendanceRecord
        );
    }

    public function destroy(
        AttendanceRecord $attendanceRecord
    ) {
        $this->authorize(
            'delete',
            $attendanceRecord
        );

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
