<?php

namespace App\Adapters;

use App\Contracts\BpjsOperatingRoomAdapterInterface;

class FakeBpjsOperatingRoomAdapter implements BpjsOperatingRoomAdapterInterface
{
    /**
     * Fetch operating room schedules from fake BPJS.
     */
    public function fetchOperatingRoomSchedules(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            [
                'bpjs_or_code' => 'OK1',
                'room_name' => 'Kamar Operasi 1',
                'schedules' => [
                    [
                        'bpjs_schedule_id' => 'SCH-OK1-1',
                        'patient_name' => 'Budi Santoso',
                        'scheduled_start_at' => "$today 08:00:00",
                        'actual_start_at' => "$today 08:05:00",
                        'status' => 'selesai',
                    ],
                    [
                        'bpjs_schedule_id' => 'SCH-OK1-2',
                        'patient_name' => 'Siti Aminah',
                        'scheduled_start_at' => "$today 10:30:00",
                        'actual_start_at' => "$today 10:45:00",
                        'status' => 'sedang_dilaksanakan',
                    ],
                    [
                        'bpjs_schedule_id' => 'SCH-OK1-3',
                        'patient_name' => 'Joko Prasetyo',
                        'scheduled_start_at' => "$today 13:00:00",
                        'actual_start_at' => null,
                        'status' => 'menunggu',
                    ],
                ]
            ],
            [
                'bpjs_or_code' => 'OK2',
                'room_name' => 'Kamar Operasi 2',
                'schedules' => [
                    [
                        'bpjs_schedule_id' => 'SCH-OK2-1',
                        'patient_name' => 'Aditya Pratama',
                        'scheduled_start_at' => "$today 09:00:00",
                        'actual_start_at' => "$today 09:10:00",
                        'status' => 'sedang_dilaksanakan',
                    ],
                    [
                        'bpjs_schedule_id' => 'SCH-OK2-2',
                        'patient_name' => 'Dewi Lestari',
                        'scheduled_start_at' => "$today 11:30:00",
                        'actual_start_at' => null,
                        'status' => 'menunggu',
                    ],
                ]
            ],
            [
                'bpjs_or_code' => 'OK3',
                'room_name' => 'Kamar Operasi 3',
                'schedules' => [
                    [
                        'bpjs_schedule_id' => 'SCH-OK3-1',
                        'patient_name' => 'Rian Hidayat',
                        'scheduled_start_at' => "$today 08:30:00",
                        'actual_start_at' => "$today 08:30:00",
                        'status' => 'selesai',
                    ],
                    [
                        'bpjs_schedule_id' => 'SCH-OK3-2',
                        'patient_name' => 'Mega Utami',
                        'scheduled_start_at' => "$today 14:00:00",
                        'actual_start_at' => null,
                        'status' => 'menunggu',
                    ],
                ]
            ],
        ];
    }
}
