<?php

namespace App\Models;

use App\System\Database\Relations\BelongsTo;

/**
 * Class News
 * @package App\Models
 *
 * @property int $ID
 * @property int $ParticipantId
 * @property string $NewsTitle
 * @property string $NewsMessage
 * @property int $LikesCounter
 * @property Participant $participant
 */
class News extends Model
{
    protected $table = 'news';

    protected $primaryKey = 'ID';

    protected $fillable = [
        'ParticipantId',
        'NewsTitle',
        'NewsMessage',
        'LikesCounter'
    ];

    /**
     * @return BelongsTo
     */
    protected function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'ID', 'ParticipantId');
    }
}