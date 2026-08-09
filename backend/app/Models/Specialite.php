<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Specialite extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'slug', 'icone', 'description', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Specialite $specialite) {
            $specialite->slug = $specialite->slug ?: Str::slug($specialite->nom);
        });
    }

    public function medecins()
    {
        return $this->belongsToMany(Medecin::class, 'medecin_specialite');
    }

    public function scopeActives($query)
    {
        return $query->where('actif', true);
    }
}
