<?php

namespace App\Http\Controllers\Api\Objects;

class UserObj
{
    public static function brief($users, $single = false)
    {
        if ($single) {
            $users = collect([$users]);
        }

        $users = $users->map(function ($user) {
            if (empty($user)) {
                return null;
            }

            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'avatar' => $user->getAvatar(),
                'profile_url' => $user->getProfileUrl(),
            ];
        });

        return $single ? $users->first() : $users;
    }
}
