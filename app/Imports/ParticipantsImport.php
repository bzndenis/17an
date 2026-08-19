<?php

namespace App\Imports;

use App\Enums\ParticipantStatus;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Services\ActivityLogService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ParticipantsImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        protected int $eventId,
        protected ActivityLogService $activityLogService,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['nama'])) {
                continue;
            }

            $categoryId = null;
            if (! empty($row['kategori'])) {
                $category = ParticipantCategory::firstOrCreate(
                    ['event_id' => $this->eventId, 'name' => $row['kategori']],
                );
                $categoryId = $category->id;
            }

            $gender = match (strtolower(trim($row['jenis_kelamin'] ?? ''))) {
                'laki-laki', 'male', 'l' => 'male',
                'perempuan', 'female', 'p' => 'female',
                default => 'male',
            };

            Participant::updateOrCreate(
                [
                    'event_id' => $this->eventId,
                    'number' => $row['nomor'] ?? Participant::where('event_id', $this->eventId)->max('number') + 1,
                ],
                [
                    'name' => $row['nama'],
                    'gender' => $gender,
                    'dob' => $row['tanggal_lahir'] ?? null,
                    'phone' => $row['nomor_hp'] ?? null,
                    'address' => $row['alamat'] ?? null,
                    'rt_rw' => $row['rt_rw'] ?? null,
                    'team' => $row['tim'] ?? null,
                    'category_id' => $categoryId,
                    'status' => ParticipantStatus::Active,
                ],
            );
        }

        $this->activityLogService->log(
            'participants.imported',
            'Import peserta dari Excel',
            ['count' => $rows->count()],
        );
    }
}
