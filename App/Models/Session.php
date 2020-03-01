<?php

namespace App\Models;

use App\Contracts\FromFillableInterface;
use App\System\Database\Relations\BelongsToMany;

/**
 * Class Session
 * @package App\Models
 *
 * @property int $ID
 * @property string $Name
 * @property string $TimeOfEvent
 * @property string $Description
 * @property int $ParticipantMaxNumber
 *
 * @property Speaker[] $speakers
 * @property Participant[] $participants
 */
class Session extends Model implements FromFillableInterface
{
    protected $table = 'session';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'Name',
        'TimeOfEvent',
        'Description',
        'ParticipantMaxNumber'
    ];

    protected $dates = [
        'TimeOfEvent'
    ];

    /**
     * @return BelongsToMany
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class, 'session_speaker', 'SessionID', 'SpeakerID');
    }

    /**
     * @return BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'participant_session', 'SessionID', 'ParticipantID');
    }

    /**
     * @return array
     */
    public function getFromFillable(): array
    {
        return $this->data + ['speakers' => $this->speakers->all()];
    }
}