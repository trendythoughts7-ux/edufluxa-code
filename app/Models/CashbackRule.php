<?php

namespace App\Models;

use App\User;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class CashbackRule extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'cashback_rules';
    public $timestamps = false;
    protected $guarded = ['id'];

    // Enums
    static $targetTypes = ['all', 'recharge_wallet', 'courses', 'store_products', 'bundles', 'meetings', 'registration_packages', 'subscription_packages'];
    static $courseTargets = ['all_courses', 'live_classes', 'video_courses', 'text_courses', 'specific_categories', 'specific_instructors', 'specific_courses'];
    static $productTargets = ['all_products', 'virtual_products', 'physical_products', 'specific_categories', 'specific_sellers', 'specific_products'];
    static $bundleTargets = ['all_bundles', 'specific_categories', 'specific_instructors', 'specific_bundles'];
    static $meetingTargets = ['all_meetings', 'specific_instructors'];
    static $subscriptionTargets = ['all_packages', 'specific_packages'];
    static $registrationPackagesTargets = ['all_packages', 'specific_packages'];


    public $translatedAttributes = ['title'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }


    // #############
    // Relations
    // ############

    public function usersAndGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CashbackRuleUserGroup::class, 'cashback_rule_id', 'id');
    }

    public function userGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'cashback_rule_users_groups', 'cashback_rule_id', 'group_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cashback_rule_users_groups', 'cashback_rule_id', 'user_id');
    }

    public function specificationItems() // used just in query
    {
        return $this->hasMany(CashbackRuleSpecificationItem::class, 'cashback_rule_id', 'id');
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'category_id');
    }

    public function instructors(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'instructor_id');
    }

    public function sellers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'seller_id');
    }

    public function webinars(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Webinar::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'webinar_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'product_id');
    }

    public function bundles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Bundle::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'bundle_id');
    }

    public function subscribes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subscribe::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'subscribe_id');
    }

    public function registrationPackages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(RegistrationPackage::class, 'cashback_rule_specification_items', 'cashback_rule_id', 'registration_package_id');
    }


    // #############
    // Helpers
    // ############

    public function getAmount($itemPrice = 1)
    {
        if ($this->amount_type == 'percent') {
            return ($itemPrice * $this->amount) / 100;
        } else {
            return $this->amount;
        }
    }
}
