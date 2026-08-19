<?php

namespace App\Exports;

use App\Models\Participant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ParticipantsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected int $eventId) {}

    public function collection(): Collection
    {
        return Participant::query()
            ->with('category')
            ->where('event_id', $this->eventId)
            ->orderBy('number')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor',
            'Nama',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Nomor HP',
            'Alamat',
            'RT/RW',
            'Tim',
            'Kategori',
            'Status',
        ];
    }

    public function map($participant): array
    {
        return [
            $participant->number,
            $participant->name,
            $participant->gender === 'male' ? 'Laki-laki' : 'Perempuan',
            $participant->dob?->format('Y-m-d'),
            $participant->phone,
            $participant->address,
            $participant->rt_rw,
            $participant->team,
            $participant->category?->name,
            $participant->status->label(),
        ];
    }
}
