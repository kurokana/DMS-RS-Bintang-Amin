<?php

namespace App\Contracts;

interface BpjsOperatingRoomAdapterInterface
{
    /**
     * Fetch operating room schedules from BPJS.
     * 
     * Returns an array of rooms and their surgery schedules:
     * [
     *     [
     *         'bpjs_or_code' => 'OK1',
     *         'room_name' => 'Kamar Operasi 1',
     *         'schedules' => [
     *             [
     *                 'bpjs_schedule_id' => 'SCH001',
     *                 'patient_name' => 'Ahmad Fauzi',
     *                 'scheduled_start_at' => '2026-07-10 08:00:00',
     *                 'actual_start_at' => '2026-07-10 08:15:00',
     *                 'status' => 'sedang_dilaksanakan', // menunggu | sedang_dilaksanakan | selesai
     *             ],
     *             ...
     *         ]
     *     ],
     *     ...
     * ]
     *
     * @return array
     * @throws \Exception
     */
    public function fetchOperatingRoomSchedules(): array;
}
