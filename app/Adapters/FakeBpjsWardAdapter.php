<?php

namespace App\Adapters;

use App\Contracts\BpjsWardAdapterInterface;

class FakeBpjsWardAdapter implements BpjsWardAdapterInterface
{
    /**
     * Fetch ward availability from fake BPJS.
     */
    public function fetchWardAvailability(): array
    {
        // Return dummy data. Bed totals are static, occupied beds fluctuate slightly
        return [
            ['bpjs_class_code' => 'VIP', 'bed_total' => 10, 'bed_occupied' => rand(4, 7)],
            ['bpjs_class_code' => 'K1', 'bed_total' => 20, 'bed_occupied' => rand(10, 16)],
            ['bpjs_class_code' => 'K2', 'bed_total' => 30, 'bed_occupied' => rand(18, 26)],
            ['bpjs_class_code' => 'K3', 'bed_total' => 50, 'bed_occupied' => rand(40, 47)],
            ['bpjs_class_code' => 'ICU', 'bed_total' => 8, 'bed_occupied' => rand(3, 6)],
            ['bpjs_class_code' => 'ISO', 'bed_total' => 6, 'bed_occupied' => rand(1, 4)],
        ];
    }
}
