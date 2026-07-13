<?php

namespace App\Contracts;

interface BpjsWardAdapterInterface
{
    /**
     * Fetch ward availability from BPJS.
     * 
     * Returns an array of ward classes and their availability:
     * [
     *     [
     *         'bpjs_class_code' => 'VIP',
     *         'bed_total' => 10,
     *         'bed_occupied' => 6,
     *     ],
     *     ...
     * ]
     *
     * @return array
     * @throws \Exception
     */
    public function fetchWardAvailability(): array;
}
