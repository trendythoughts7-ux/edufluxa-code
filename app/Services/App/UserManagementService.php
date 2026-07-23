<?php

namespace App\Services\App;

use App\User;
use App\Models\Role;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\BecomeInstructor;
use App\Models\UserMeta;

class UserManagementService
{
    public function determineEmailOrMobile($data)
    {
        $email_regex = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
        $username = 'mobile';
        if (preg_match($email_regex, request('email_or_mobile', null))) {
            $username = 'email';
        }
        return $username;
    }

    public function createUser($data, $emailOrMobile)
    {
        if (empty($data['role_id'])) {
            return null;
        }
        $role = Role::find($data['role_id']);
        if (empty($role)) {
            return null;
        }
        $referralSettings = getReferralSettings();
        $usersAffiliateStatus = (!empty($referralSettings) and !empty($referralSettings['users_affiliate_status']));
        $user = User::create([
            'full_name' => $data['full_name'],
            'role_name' => $role->name,
            'role_id' => $data['role_id'],
            $emailOrMobile => $data[$emailOrMobile],
            'password' => User::generatePassword($data['password']),
            'status' => $data['status'],
            'affiliate' => $usersAffiliateStatus,
            'verified' => true,
            'created_at' => time(),
        ]);
        if (!empty($data['group_id'])) {
            $group = Group::find($data['group_id']);
            if (!empty($group)) {
                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'created_at' => time(),
                ]);
                $notifyOptions = [
                    '[u.g.title]' => $group->name,
                ];
                sendNotification("add_to_user_group", $notifyOptions, $user->id);
            }
        }
        return $user;
    }

    public function updateUser($user, $data)
    {
        $userOldRoleId = $user->role_id;
        $userRoleName = $user->role_name;
        $userRoleId = $user->role_id;
        $userRoleCaption = null;
        if (auth()->user()->can('admin_update_user_role_in_edit_page') and !empty($data['role_id'])) {
            $role = Role::where('id', $data['role_id'])->first();
            if (empty($role)) {
                return false;
            }
            $userRoleName = $role->name;
            $userRoleId = $role->id;
            $userRoleCaption = $role->caption;
            if ($user->role_id != $role->id and $role->name == Role::$teacher) {
                $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'pending_pay_package'])
                    ->first();
                if (!empty($becomeInstructor)) {
                    $becomeInstructor->update([
                        'status' => 'accept'
                    ]);
                    // Send Notification
                    $becomeInstructor->sendNotificationToUser('accept');
                }
            }
        }
        $user->full_name = !empty($data['full_name']) ? $data['full_name'] : null;
        $user->username = $data['username'];
        $user->role_name = $userRoleName;
        $user->role_id = $userRoleId;
        $user->timezone = $data['timezone'] ?? null;
        $user->currency = $data['currency'] ?? null;
        $user->organ_id = !empty($data['organ_id']) ? $data['organ_id'] : null;
        $user->email = !empty($data['email']) ? $data['email'] : null;
        $user->mobile = !empty($data['mobile']) ? $data['mobile'] : null;
        $user->bio = !empty($data['bio']) ? $data['bio'] : null;
        $user->about = !empty($data['about']) ? $data['about'] : null;
        $user->status = !empty($data['status']) ? $data['status'] : null;
        $user->language = !empty($data['language']) ? $data['language'] : null;
        if (!empty($data['password'])) {
            $user->password = User::generatePassword($data['password']);
        }
        if (!empty($data['ban']) and $data['ban'] == '1') {
            $ban_start_at = strtotime($data['ban_start_at']);
            $ban_end_at = strtotime($data['ban_end_at']);
            $user->ban = true;
            $user->ban_start_at = $ban_start_at;
            $user->ban_end_at = $ban_end_at;
        } else {
            $user->ban = false;
            $user->ban_start_at = null;
            $user->ban_end_at = null;
        }
        $user->verified = (!empty($data['verified']) and $data['verified'] == '1');
        $user->affiliate = (!empty($data['affiliate']) and $data['affiliate'] == '1');
        $user->can_create_store = (!empty($data['can_create_store']) and $data['can_create_store'] == '1');
        $user->access_content = (!empty($data['access_content']) and $data['access_content'] == '1');
        $user->enable_ai_content = (!empty($data['enable_ai_content']) and $data['enable_ai_content'] == '1');
        $user->public_message = (!empty($data['public_message']) and $data['public_message'] == '1');
        $user->enable_profile_statistics = (!empty($data['enable_profile_statistics']) and $data['enable_profile_statistics'] == '1');
        $user->auto_renew_subscription = (!empty($data['auto_renew_subscription']) and $data['auto_renew_subscription'] == '1');
        $user->save();
        // save certificate_additional in user metas table
        $this->handleUserCertificateAdditional($user->id, $data['certificate_additional']);
        if ($userOldRoleId != $userRoleId) {
            $notifyOptions = [
                '[u.role]' => $userRoleCaption,
            ];
            sendNotification("user_role_change", $notifyOptions, $user->id);
        }
        return true;
    }

    private function handleUserCertificateAdditional($userId, $value)
    {
        $name = 'certificate_additional';
        if (empty($value)) {
            $checkMeta = UserMeta::where('user_id', $userId)
                ->where('name', $name)
                ->first();
            if (!empty($checkMeta)) {
                $checkMeta->delete();
            }
        } else {
            UserMeta::updateOrCreate([
                'user_id' => $userId,
                'name' => $name
            ], [
                'value' => $value
            ]);
        }
    }
}
