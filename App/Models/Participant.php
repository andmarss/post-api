<?php

namespace App\Models;

class Participant extends Model
{
    protected $table = 'participant';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'Email', 'Name'
    ];

    public function news()
    {
        return $this->hasMany(News::class, 'ParticipantId', 'ID');
    }
}