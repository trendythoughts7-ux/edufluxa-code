<?php

namespace App\Services\App;

use App\Models\UserCommission;

class UserCommissionService
{
    public function storeUserCommissions($user, $data)
    {
        $user->commissions()->delete();

        if (!empty($data['commissions'])) {
            $insert = [];

            foreach ($data['commissions'] as $source => $commission) {
                if (!empty($commission['type']) and !empty($commission['value'])) {
                    $value = $commission['value'];

                    if ($commission['type'] == "fixed_amount") {
                        $value = convertPriceToDefaultCurrency($value);
                    }

                    $insert[] = [
                        'user_id' => $user->id,
                        'user_group_id' => null,
                        'source' => $source,
                        'type' => $commission['type'],
                        'value' => $value,
                    ];
                }
            }

            if (!empty($insert)) {
                UserCommission::query()->insert($insert);
            }
        }
    }
}
