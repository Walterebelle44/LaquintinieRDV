<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedecinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prenom' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'telephone' => ['required', 'string', 'unique:users,telephone'],
            'numero_ordre' => ['required', 'string', 'unique:medecins,numero_ordre'],
            'specialite_ids' => ['required', 'array', 'min:1'],
            'specialite_ids.*' => ['exists:specialites,id'],
            'biographie' => ['nullable', 'string'],
            'annees_experience' => ['nullable', 'integer', 'min:0'],
            'tarif_consultation' => ['nullable', 'integer', 'min:0'],
            'duree_consultation_defaut' => ['nullable', 'integer', 'min:5'],
        ];
    }
}
