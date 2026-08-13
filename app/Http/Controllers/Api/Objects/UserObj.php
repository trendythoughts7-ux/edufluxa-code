<?php

namespace App\Http\Controllers\Api\Objects;

use App\Models\Badge;

/**
 * UserObj
 *
 * Canonical formatter for "brief" user data returned by public-facing API
 * endpoints (blog authors, comment authors, search results, etc).
 *
 * Design goals:
 *  - Single source of truth: every endpoint that needs a lightweight user
 *    representation should call UserObj::brief() instead of hand-rolling
 *    its own ['full_name' => ..., 'avatar' => ...] array.
 *  - Cheap by default: the base shape (id, full_name, avatar, profile_url)
 *    touches no relations and issues no extra queries beyond what's already
 *    loaded on the model.
 *  - Opt-in expensive fields: 'title', 'badges', and 'courses_count' each
 *    require their own query per user. They are only computed when the
 *    caller explicitly asks for them via $with, so existing call-sites that
 *    only need the base shape see zero performance change.
 *  - Batch-safe: when $with includes 'badges' or 'courses_count' and $users
 *    is a collection of many users (e.g. a paginated blog list with several
 *    distinct authors), the underlying queries are issued once per UNIQUE
 *    user id, not once per row - avoiding N+1 blow-up on list endpoints.
 *
 * @see \App\Models\Api\User::getBriefAttribute() for the older, heavier
 *      "brief" shape used internally (dashboard/panel contexts). UserObj is
 *      intentionally the lighter, public-facing counterpart and the two are
 *      NOT meant to return identical shapes - keep them separate.
 */
class UserObj
{
    /**
     * Fields that require an extra query and are therefore opt-in only.
     */
    private const EXPENSIVE_FIELDS = ['title', 'badges', 'courses_count'];

    /**
     * Build the "brief" representation of one user or a collection of users.
     *
     * @param  \Illuminate\Support\Collection|\App\User|\App\Models\Api\User|null  $users
     *         A single user model, or a collection of user models.
     * @param  bool  $single
     *         Pass true when $users is a single model rather than a collection.
     * @param  array  $with
     *         Optional extra fields to include: any of 'title', 'badges',
     *         'courses_count'. Leave empty for the fast/default shape.
     * @return \Illuminate\Support\Collection|array|null
     *         A collection of brief arrays (or a single array when $single),
     *         null entries preserved for missing users.
     */
    public static function brief($users, $single = false, array $with = [])
    {
        $collection = $single ? collect([$users]) : $users;

        $with = array_intersect($with, self::EXPENSIVE_FIELDS);

        // Pre-compute expensive, batchable fields once per UNIQUE user id
        // so a list of N users with only a handful of distinct authors
        // doesn't pay N separate queries.
        $badgesByUserId = [];
        $courseCountByUserId = [];

        if (in_array('badges', $with, true) || in_array('courses_count', $with, true)) {
            $uniqueUsers = $collection
                ->filter()
                ->unique(function ($user) {
                    return $user->id;
                });

            foreach ($uniqueUsers as $user) {
                if (in_array('badges', $with, true)) {
                    $badgesByUserId[$user->id] = collect(Badge::getUserBadges($user, true, false))
                        ->filter()
                        ->map(function ($badge) {
                            return [
                                'id' => $badge->id,
                                'title' => $badge->title,
                                'image' => url($badge->image),
                            ];
                        })
                        ->values()
                        ->all();
                }

                if (in_array('courses_count', $with, true)) {
                    $courseCountByUserId[$user->id] = $user->getActiveWebinars(true);
                }
            }
        }

        $result = $collection->map(function ($user) use ($with, $badgesByUserId, $courseCountByUserId) {
            if (empty($user)) {
                return null;
            }

            $brief = [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'avatar' => $user->getAvatar(),
                'profile_url' => $user->getProfileUrl(),
            ];

            if (in_array('title', $with, true)) {
                $brief['title'] = $user->level_of_training;
            }

            if (in_array('badges', $with, true)) {
                $brief['badges'] = $badgesByUserId[$user->id] ?? [];
            }

            if (in_array('courses_count', $with, true)) {
                $brief['courses_count'] = $courseCountByUserId[$user->id] ?? 0;
            }

            return $brief;
        });

        return $single ? $result->first() : $result;
    }
}
