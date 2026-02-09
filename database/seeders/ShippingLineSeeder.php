<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingLine;

class ShippingLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shippingLines = [
            [
                'shipping_line_name' => 'Maersk Line Saudi Arabia',
                'contact_info' => '+966920000123 | info@maersk-saudi.com',
                'notes' => 'Global container shipping company',
            ],
            [
                'shipping_line_name' => 'MSC Saudi Agency',
                'contact_info' => '+966920000456 | saudi@msc.com',
                'notes' => 'Mediterranean Shipping Company local agent',
            ],
            [
                'shipping_line_name' => 'CMA CGM Saudi Arabia',
                'contact_info' => '+966920000789 | contact@cma-cgm-saudi.com',
                'notes' => 'French container shipping line',
            ],
            [
                'shipping_line_name' => 'Hapag-Lloyd Saudi',
                'contact_info' => '+966920000012 | saudi@hapag-lloyd.com',
                'notes' => 'German shipping company local office',
            ],
            [
                'shipping_line_name' => 'Saudi Arabian Shipping',
                'contact_info' => '+966920000345 | info@saudi-shipping.com',
                'notes' => 'Local Saudi shipping company',
            ],
        ];

        foreach ($shippingLines as $shippingLine) {
            ShippingLine::create($shippingLine);
        }
    }
}
