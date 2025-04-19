<?php

namespace Partymeister\Slides\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Kra8\Snowflake\HasShortflakePrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Motor\Backend\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
use Motor\Media\Models\FileAssociation;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

/**
 * Partymeister\Slides\Models\PlaylistItem
 *
 * @property int $id
 * @property int $playlist_id
 * @property string $type
 * @property int $sort_position
 * @property string $slide_type
 * @property int|null $slide_id
 * @property int $duration
 * @property int|null $transition_id
 * @property int $transition_duration
 * @property int $is_advanced_manually
 * @property int $is_muted
 * @property int $midi_note
 * @property mixed $metadata
 * @property string $callback_hash
 * @property int $callback_delay
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read User|null $eraser
 * @property-read FileAssociation $file_association
 * @property-read Slide|null $slide
 * @property-read Transition|null $transition
 * @property-read User $updater
 *
 * @method static Builder|PlaylistItem filteredBy(Filter $filter, $column)
 * @method static Builder|PlaylistItem filteredByMultiple(Filter $filter)
 * @method static Builder|PlaylistItem newModelQuery()
 * @method static Builder|PlaylistItem newQuery()
 * @method static Builder|PlaylistItem query()
 * @method static Builder|PlaylistItem search($q, $full_text = false)
 * @method static Builder|PlaylistItem whereCallbackDelay($value)
 * @method static Builder|PlaylistItem whereCallbackHash($value)
 * @method static Builder|PlaylistItem whereCreatedAt($value)
 * @method static Builder|PlaylistItem whereCreatedBy($value)
 * @method static Builder|PlaylistItem whereDeletedBy($value)
 * @method static Builder|PlaylistItem whereDuration($value)
 * @method static Builder|PlaylistItem whereId($value)
 * @method static Builder|PlaylistItem whereIsAdvancedManually($value)
 * @method static Builder|PlaylistItem whereIsMuted($value)
 * @method static Builder|PlaylistItem whereMetadata($value)
 * @method static Builder|PlaylistItem whereMidiNote($value)
 * @method static Builder|PlaylistItem wherePlaylistId($value)
 * @method static Builder|PlaylistItem whereSlideId($value)
 * @method static Builder|PlaylistItem whereSlideType($value)
 * @method static Builder|PlaylistItem whereSortPosition($value)
 * @method static Builder|PlaylistItem whereTransitionDuration($value)
 * @method static Builder|PlaylistItem whereTransitionId($value)
 * @method static Builder|PlaylistItem whereType($value)
 * @method static Builder|PlaylistItem whereUpdatedAt($value)
 * @method static Builder|PlaylistItem whereUpdatedBy($value)
 * @mixin Eloquent
 */
class PlaylistItem extends Model
{
    use Searchable;
    use Filterable;
    use BlameableTrait;
    use HasShortflakePrimary;

    /**
     * Searchable columns for the searchable trait
     *
     * @var array
     */
    protected $searchableColumns = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'playlist_id',
        'type',
        'slide_type',
        'slide_id',
        'duration',
        'transition_id',
        'transition_slidemeister_id',
        'transition_duration',
        'is_advanced_manually',
        'is_muted',
        'midi_note',
        'metadata',
        'callback_hash',
        'callback_delay',
        'sort_position',
    ];

    protected $casts = ['metadata' => 'json'];

    /**
     * @return BelongsTo
     */
    /**
     * @return BelongsTo
     */
    public function transition()
    {
        return $this->belongsTo(Transition::class);
    }

    /**
     * @return BelongsTo
     */
    /**
     * @return BelongsTo
     */
    public function transition_slidemeister()
    {
        return $this->belongsTo(Transition::class, 'transition_slidemeister_id');
    }

    /**
     * @return MorphOne
     */
    /**
     * @return MorphOne
     */
    public function file_association()
    {
        return $this->morphOne(FileAssociation::class, 'model');
    }

    /**
     * @return BelongsTo
     */
    /**
     * @return BelongsTo
     */
    public function slide()
    {
        return $this->belongsTo(Slide::class);
    }
}
