<?php

namespace App\Models;

use App\System\Database\Relations\BelongsToMany;

/**
 * Class Speaker
 * @package App\Models
 *
 * @property string $name
 * @property Session[] $sessions
 */
class Speaker extends Model
{
    protected $table = 'speaker';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'Name'
    ];

    /**
     * @return BelongsToMany
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_speaker', 'SpeakerID', 'SessionID');
    }
}