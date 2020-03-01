<?php

namespace App\Models;
/**
 * Class Participant
 * @package App\Models
 *
 * @property int ID
 * @property string Email
 * @property string Name
 */
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