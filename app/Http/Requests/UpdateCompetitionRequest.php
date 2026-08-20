<?php

namespace App\Http\Requests;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'system' => ['required', Rule::enum(CompetitionSystem::class)],
            'status' => ['nullable', Rule::enum(CompetitionStatus::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'prize_1' => ['nullable', 'string', 'max:255'],
            'prize_2' => ['nullable', 'string', 'max:255'],
            'prize_3' => ['nullable', 'string', 'max:255'],
            'banner' => ['nullable', 'image', 'max:4096'],
            'config' => ['nullable', 'array'],
            'players_per_side' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lomba wajib diisi.',
            'system.required' => 'Sistem pertandingan wajib dipilih.',
        ];
    }
}
