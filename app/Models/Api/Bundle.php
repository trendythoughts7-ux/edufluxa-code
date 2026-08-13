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
 * silent no-op for Bundle, not a crash. Declared here to accurately
 * reflect current behavior. If Bundle should support prerequisites or
 * a sale start_date in the future, that is a real feature addition
 * (new relation/column), not a phpstan-only fix - flagging for a
 * deliberate product decision rather than assuming one.
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
