<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'telephone' => ['required', 'string', 'max:30', 'unique:users,telephone'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'sexe' => ['nullable', 'in:M,F,Autre'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Un compte existe déjà avec cet email.',
            'telephone.unique' => 'Un compte existe déjà avec ce numéro de téléphone.',
            'mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}
