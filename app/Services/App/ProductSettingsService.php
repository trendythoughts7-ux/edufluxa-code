<?php
namespace App\Services\App;

use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductSettingsService
{
    public function getSettings()
    {
        removeContentLocale();
        $setting = Setting::where('page', 'general')
            ->where('name', Setting::$storeSettingsName)
            ->first();
        if (!empty($setting)) {
            $setting->value = json_decode($setting->value, true);
        }
        return [
            'itemValue' => !empty($setting) ? $setting->value : null,
        ];
    }

    public function storeSettings(Request $request)
    {
        $page = 'general';
        $name = Setting::$storeSettingsName;
        $data = $request->all();
        $locale = $request->get('locale', Setting::$defaultSettingsLocale);
        $newValues = $data['value'];
        $values = [];
        $validator = Validator::make($data['value'], [
            'exchangeable_unit' => 'required_if:exchangeable,1',
        ]);
        $validator->validate();
        $settings = Setting::where('name', $name)->first();
        if (!empty($settings) and !empty($settings->value)) {
            $values = json_decode($settings->value);
        }
        if (!empty($newValues) and !empty($values)) {
            foreach ($newValues as $newKey => $newValue) {
                foreach ($values as $key => $value) {
                    if ($key == $newKey) {
                        $values->$key = $newValue;
                        unset($newValues[$key]);
                    }
                }
            }
        }
        if (!empty($newValues)) {
            $values = array_merge((array)$values, $newValues);
        }
        $settings = Setting::updateOrCreate(
            ['name' => $name],
            [
                'page' => $page,
                'updated_at' => time(),
            ]
        );
        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $settings->id,
                'locale' => mb_strtolower($locale)
            ],
            [
                'value' => json_encode($values),
            ]
        );
        cache()->forget('settings.' . $name);
    }
}
