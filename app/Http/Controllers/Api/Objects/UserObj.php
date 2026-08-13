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
 *  - N+1-proof regardless of call pattern: badges and courses_count are
 *    memoized in a static, request-scoped cache keyed by user id. This
 *    protects both call patterns used across the codebase:
 *      (a) bulk collection calls, e.g. UserObj::brief($users, false, [...])
 *          where $users has many distinct authors, and
 *      (b) repeated single calls from a per-model accessor invoked in a
 *          loop, e.g. Blog::getDetailsAttribute() calling
 *          UserObj::brief($this->author, true, [...]) once per blog while
 *          mapping a list of blogs.
 *    Without this cache, pattern (b) would issue one badges/courses query
 *    PER ROW even for repeat authors, since each call only "sees" a single
 *    user and has no visibility into sibling rows. The static cache makes
 *    the second and further calls for the same user id free, no matter
 *    which pattern triggered them. PHP-FPM/Laravel serves one HTTP request
 *    per process lifecycle, so this cache naturally resets between
 *    requests and never leaks data across users.
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
     * Request-scoped memoization caches, keyed by user id.
     * Reset automatically at the start of every new HTTP request/process.
     */
    private static array $badgesCache = [];
    private static array $courseCountCache = [];

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

        $needsBadges = in_array('badges', $with, true);
        $needsCourseCount = in_array('courses_count', $with, true);

        if ($needsBadges || $needsCourseCount) {
            $uniqueUsers = $collection
                ->filter()
                ->unique(function ($user) {
                    return $user->id;
                });

            foreach ($uniqueUsers as $user) {
                if ($needsBadges && !array_key_exists($user->id, self::$badgesCache)) {
                    self::$badgesCache[$user->id] = collect(Badge::getUserBadges($user, true, false))
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

                if ($needsCourseCount && !array_key_exists($user->id, self::$courseCountCache)) {
                    self::$courseCountCache[$user->id] = $user->getActiveWebinars(true);
                }
            }
        }

        $result = $collection->map(function ($user) use ($with, $needsBadges, $needsCourseCount) {
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

            if ($needsBadges) {
                $brief['badges'] = self::$badgesCache[$user->id] ?? [];
            }

            if ($needsCourseCount) {
                $brief['courses_count'] = self::$courseCountCache[$user->id] ?? 0;
            }

            return $brief;
        });

        return $single ? $result->first() : $result;
    }
}
