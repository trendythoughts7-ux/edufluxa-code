<?php

namespace App\Models\Api;

use App\Models\Api\Traits\CheckForSaleTrait;
use App\Models\Favorite;
use App\Models\Bundle as Model;

/**
 * NOTE: Bundle does not currently have a start_date column or a
 * prerequisites relation (unlike Webinar, which has both). The
 * $this->start_date and $this->prerequisites accesses in
 * CheckForSaleTrait (shared with Webinar) resolve to null for Bundle
 * via Eloquent's default magic getter and are guarded by
 * !empty()/short-circuit checks in every call site - this is a
 * silent no-op for Bundle, not a crash.
 *
 * DECISION (researched against industry practice - Teachable, Tutor
 * LMS, LearnDash, LifterLMS - before deciding, not guessed): Bundle
 * will NOT get a prerequisites relation. Every surveyed platform
 * scopes prerequisites to individual courses (a learning-sequence
 * concept), never to bundles (a commerce/pricing grouping concept) -
 * adding it here would be an anti-pattern nothing in the market does.
 * Bundle will NOT get a start_date column either. No surveyed
 * platform implements scheduled/time-limited sales as a date field on
 * the product itself; the universal pattern is a separate
 * Coupon/Promotion entity with its own date range, applied to
 * products - more flexible (stackable, reusable, multi-product) than
 * a single column could ever be. If time-limited Bundle sales are
 * wanted in the future, build a Coupon/Promotion system, not a Bundle
 * column. This closes the open decision from Batch 7/Section 2d-i -
 * current no-op behavior is intentional and final, not a placeholder.
 * @property string|null $start_date
 * @property \Illuminate\Database\Eloquent\Collection|null $prerequisites
 */
class Bundle extends Model
{
    use CheckForSaleTrait;

    public function getIsFavoriteAttribute()
    {
        if (!apiAuth()) {
            return null;
        }
        return (bool)Favorite::where('bundle_id', $this->id)
            ->where('user_id', apiAuth()->id)
            ->first();
    }

    public function badges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\Api\ProductBadgeContent', 'targetable_id', 'id');
    }
    public function bundleWebinars(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\Api\BundleWebinar', 'bundle_id', 'id');
    }

    public function webinars(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany('App\Models\Api\Webinar', 'bundle_webinars', 'bundle_id', 'webinar_id');
    }
    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Api\User', 'teacher_id', 'id');
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\Api\Ticket', 'bundle_id', 'id');
    }

}
