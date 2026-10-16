<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'role', 'description', 'image'])]
class Npc extends Model
{
    /**
     * Otomatis sertakan atribut image_url di respon JSON
     */
    protected $appends = ['image_url'];

    /**
     * Accessor untuk mendapatkan URL publik gambar dari storage
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}

