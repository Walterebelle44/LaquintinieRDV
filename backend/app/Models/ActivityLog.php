<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'auteur_libelle', 'action', 'type',
        'entite_type', 'entite_id', 'ip_address', 'user_agent', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre une entrée de log applicatif (traçabilité / audit admin).
     */
    public static function enregistrer(string $action, string $type = 'info', ?\Illuminate\Database\Eloquent\Model $entite = null, array $meta = []): self
    {
        $user = auth()->user();

        return static::create([
            'user_id' => $user?->id,
            'auteur_libelle' => $user?->email ?? 'système',
            'action' => $action,
            'type' => $type,
            'entite_type' => $entite ? get_class($entite) : null,
            'entite_id' => $entite?->id,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'meta' => $meta,
        ]);
    }
}
