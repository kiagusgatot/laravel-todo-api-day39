<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_completed',
        'due_date'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'date',
    ];

    // Relasi: Todo ini milik satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope: filter todo yang belum selesai
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    // Scope: filter todo yang sudah selesai
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }
}