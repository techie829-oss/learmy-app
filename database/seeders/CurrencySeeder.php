<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'INR', 'symbol' => '₹', 'decimals' => 2, 'exchange_rate' => 1.0, 'is_default' => true, 'enabled' => true],
            ['code' => 'USD', 'symbol' => '$', 'decimals' => 2, 'exchange_rate' => 0.012, 'is_default' => false, 'enabled' => true],
            ['code' => 'EUR', 'symbol' => '€', 'decimals' => 2, 'exchange_rate' => 0.011, 'is_default' => false, 'enabled' => true],
            ['code' => 'GBP', 'symbol' => '£', 'decimals' => 2, 'exchange_rate' => 0.0095, 'is_default' => false, 'enabled' => true],
            ['code' => 'BDT', 'symbol' => '৳', 'decimals' => 2, 'exchange_rate' => 1.33, 'is_default' => false, 'enabled' => true],
        ];

        foreach ($currencies as $row) {
            Currency::updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}
