<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parametre extends Model
{
    protected $fillable = ['cle', 'valeur', 'libelle'];

    public static function get(string $cle, mixed $defaut = null): mixed
    {
        return Cache::rememberForever("parametre:{$cle}", function () use ($cle, $defaut) {
            return static::where('cle', $cle)->value('valeur') ?? $defaut;
        });
    }

    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget("parametre:{$cle}");
    }
}
