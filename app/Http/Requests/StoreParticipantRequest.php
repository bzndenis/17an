<?php

namespace App\Http\Requests;

use App\Enums\ParticipantStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = session('active_event_id');

        return [
            'category_id' => ['nullable', 'exists:participant_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('participants', 'number')->where(fn ($q) => $q->where('event_id', $eventId)),
            ],
            'gender' => ['required', 'in:male,female'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'rt_rw' => ['nullable', 'string', 'max:20'],
            'team' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(ParticipantStatus::class)],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama peserta wajib diisi.',
            'number.required' => 'Nomor peserta wajib diisi.',
            'number.unique' => 'Nomor peserta sudah digunakan pada event ini.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
        ];
    }
}
