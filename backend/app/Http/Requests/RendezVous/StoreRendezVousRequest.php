<?php

namespace App\Http\Requests\RendezVous;

use Illuminate\Foundation\Http\FormRequest;

class StoreRendezVousRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // contrôlé par le middleware auth:sanctum + role sur la route
    }

    public function rules(): array
    {
        return [
            'medecin_id' => ['required', 'exists:medecins,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'motif' => ['nullable', 'string', 'max:255'],
        ];
    }
}
