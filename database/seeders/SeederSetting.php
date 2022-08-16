<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SeederSetting extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = $this->getSettingList();
        $this->createSetting($settings);
    }

    /**
     * Get available application options list
     *
     * @return string[][]
     */
    private function getSettingList(): array
    {
        $settings = [

            [
                'delivery_price' => 4,
                'delivery_price_km' => 1
            ]

        ];
        return $settings;
    }

    /**
     * Create settings
     *
     * @param array $settings
     */
    private function createSetting(array $settings): void
    {
        foreach ($settings as $setting) {
            $sett = new Setting(Arr::except($setting, []));
            $sett->save();
        }
    }
}
