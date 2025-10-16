<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(string $activityKey, string $message, Request $request, string $relatedTable): void
    {

        $activityConfig = config("activitylogger.activities.$activityKey");

        if (!$activityConfig) {
            Log::error("ActivityLogger: Activity key '$activityKey' not found in configuration.");
            return;
        }

        $activityType = $activityConfig['activity_type'] ?? 'Unknown';
        $status = $activityConfig['status'] ?? null;
        if (!$status) {
            Log::error("ActivityLogger: Status not found for activity key '$activityKey'.");
            return;
        }

        $userId = $request->header('id', null);
        $role = 'guest'; 
        if ($userId) {
            $user = User::find($userId); 
            if ($user) {
                $role = $user->role;
            }
        }

        if (empty($status) || empty($relatedTable)) {
            throw new \InvalidArgumentException("Invalid parameters provided for logging activity.");
        }

        try {
            ActivityLog::create([ 
                'user_id' => $userId,
                'role' => $role,
                'activity_type' => $activityType,
                'status' => $status,
                'message' => $message, 
                'related_table' => $relatedTable,
                'ip_address' => $request->ip() ?? '127.0.0.1', 
            ]);
        } catch (\Exception $e) {
            Log::error("ActivityLogger: Failed to log activity '$activityKey'. Error: {$e->getMessage()}");
        }
    }


    public static function beforeAuthLog(string $activityKey, string $message, Request $request, string $relatedTable): void
    {
        $activityConfig = config("activitylogger.activities.$activityKey");

        if (!$activityConfig) {
            Log::error("ActivityLogger: Activity key '$activityKey' not found in configuration.");
            return;
        }

        $status = $activityConfig['status'] ?? 'Unknown';
        $activityType = $activityConfig['activity_type'] ?? 'Unknown';

        $userId = null;
        $role = 'guest'; 

        if ($request->has('email')) {
            $user = User::where('email', $request->input('email'))->first();
            if ($user) {
                $userId = $user->id;
                $role = $user->role;
            }
        }

        try {
            ActivityLog::create([
                'user_id' => $userId,
                'role' => $role,
                'activity_type' => $activityType,
                'status' => $status,
                'message' => $message,
                'related_table' => $relatedTable,
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            Log::error("ActivityLogger: Failed to log activity '$activityKey'. Error: {$e->getMessage()}");
        }
    }


    public static function socialAuthLog(string $activityKey, string $message, ?User $user, string $relatedTable): void
    {
        $activityConfig = config("activitylogger.activities.$activityKey");

        if (!$activityConfig) {
            Log::error("ActivityLogger: Activity key '$activityKey' not found in configuration.");
            return;
        }

        $status = $activityConfig['status'] ?? 'Unknown';
        $activityType = $activityConfig['activity_type'] ?? 'Unknown';

        $userId = $user ? $user->id : null;
        $role = $user ? $user->role : 'guest';

        try {
            ActivityLog::create([
                'user_id' => $userId,
                'role' => $role,
                'activity_type' => $activityType,
                'status' => $status,
                'message' => $message,
                'related_table' => $relatedTable,
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            Log::error("ActivityLogger: Failed to log activity '$activityKey'. Error: {$e->getMessage()}");
        }
    }
}
