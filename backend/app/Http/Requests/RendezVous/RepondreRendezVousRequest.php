<?php

namespace App\Http\Requests\RendezVous;

use Illuminate\Foundation\Http\FormRequest;

class RepondreRendezVousRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:accepter,refuser,reprogrammer'],
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:255'],
            'nouvelle_date' => ['required_if:action,reprogrammer', 'nullable', 'date', 'after_or_equal:today'],
            'nouvelle_heure' => ['required_if:action,reprogrammer', 'nullable', 'date_format:H:i'],
        ];
    }
}
