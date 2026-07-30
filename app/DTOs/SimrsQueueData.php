<?php

namespace App\DTOs;

class SimrsQueueData
{
    public function __construct(
        public string $id,
        public string $polyclinicId,
        public string $doctorId,
        public int $queueNumber,
        public string $patientName,
        public string $status,
        public string $queueDate,
        public ?string $calledAt = null,
        public ?string $completedAt = null,
        public bool $isFallback = false,
    ) {}

    public static function fromArray(array $data, bool $isFallback = false): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            polyclinicId: (string) ($data['polyclinic_id'] ?? $data['poly_id'] ?? ''),
            doctorId: (string) ($data['doctor_id'] ?? ''),
            queueNumber: (int) ($data['queue_number'] ?? $data['queue_no'] ?? 0),
            patientName: (string) ($data['patient_name'] ?? 'Pasien Anonim'),
            status: (string) ($data['status'] ?? 'menunggu'),
            queueDate: (string) ($data['queue_date'] ?? date('Y-m-d')),
            calledAt: isset($data['called_at']) ? (string) $data['called_at'] : null,
            completedAt: isset($data['completed_at']) ? (string) $data['completed_at'] : null,
            isFallback: $isFallback,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'polyclinic_id' => $this->polyclinicId,
            'doctor_id' => $this->doctorId,
            'queue_number' => $this->queueNumber,
            'patient_name' => $this->patientName,
            'status' => $this->status,
            'queue_date' => $this->queueDate,
            'called_at' => $this->calledAt,
            'completed_at' => $this->completedAt,
            'is_fallback' => $this->isFallback,
        ];
    }
}
