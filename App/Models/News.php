<?php

namespace App\Models;

use App\Contracts\FromFillableInterface;
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
class News extends Model implements FromFillableInterface
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

    /**
     * @return array
     */
    public function getFromFillable(): array
    {
        return $this->data;
    }
}