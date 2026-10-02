<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    /**
     * 管理者はすべて許可
     */
    public function before(User $user): ?bool
    {
        if ($user->admin_status) {
            return true;
        }

        return null;
    }

    /**
     * 更新
     */
    public function update(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * 削除
     */
    public function delete(
        User $user,
        AttendanceRecord $attendanceRecord
    ): bool {
        return $user->id === $attendanceRecord->user_id;
    }
}
