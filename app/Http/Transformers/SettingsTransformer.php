<?php
namespace App\Http\Transformers;

use App\Http\Transformers;

class SettingsTransformer extends Transformer
{
    /**
     * Transform
     *
     * @param array $data
     * @return array
     */
    public function transform($item)
    {
        if(is_array($item))
        {
            $item = (object)$item;
        }

        return [
            "settingsId" => (int) $item->id, "settingsGhostMode" =>  $item->ghost_mode, "settingsInterested" =>  $item->interested, "settingsAgeStartRange" =>  $item->age_start_range, "settingsAgeEndRange" =>  $item->age_end_range, "settingsDistance" =>  $item->distance, "settingsCreatedAt" =>  $item->created_at, "settingsUpdatedAt" =>  $item->updated_at, 
        ];
    }

    /**
     * Show Settings
     * 
     * @param object $setting
     * @return array
     */
    public function showSettings($setting)
    {
        if($setting)
        {
            return [
                'ghost_mode'        => (int) $setting->ghost_mode,
                'interested'        => $setting->interested,
                'age_start_range'   => (int) $setting->age_start_range,
                'age_end_range'     => (int) $setting->age_end_range,
                'distance'          => (int) $setting->distance
            ];
        }

        return [];
    }
}