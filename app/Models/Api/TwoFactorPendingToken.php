<?php
namespace App\Models\Api;
use Illuminate\Database\Eloquent\Model;
class TwoFactorPendingToken extends Model
{
    protected $table = "two_factor_pending_tokens";
    protected $fillable = ["user_id", "token_hash", "expires_at"];
    protected $casts = [
        "expires_at" => "datetime",
    ];
}
