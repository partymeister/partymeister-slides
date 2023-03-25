<?php

namespace Partymeister\Slides\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Kra8\Snowflake\HasShortFlakePrimary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Motor\Backend\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
use RichanFongdasen\EloquentBlameable\BlameableTrait;

/**
 * Partymeister\Slides\Models\Transition
 *
 * @property int $id
 * @property string $name
 * @property int $identifier
 * @property int $default_duration
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read User|null $eraser
 * @property-read User $updater
 *
 * @method static Builder|Transition filteredBy(Filter $filter, $column)
 * @method static Builder|Transition filteredByMultiple(Filter $filter)
 * @method static Builder|Transition newModelQuery()
 * @method static Builder|Transition newQuery()
 * @method static Builder|Transition query()
 * @method static Builder|Transition search($q, $full_text = false)
 * @method static Builder|Transition whereCreatedAt($value)
 * @method static Builder|Transition whereCreatedBy($value)
 * @method static Builder|Transition whereDefaultDuration($value)
 * @method static Builder|Transition whereDeletedBy($value)
 * @method static Builder|Transition whereId($value)
 * @method static Builder|Transition whereIdentifier($value)
 * @method static Builder|Transition whereName($value)
 * @method static Builder|Transition whereUpdatedAt($value)
 * @method static Builder|Transition whereUpdatedBy($value)
 * @mixin Eloquent
 */
class Transition extends Model
{
    use Searchable;
    use Filterable;
    use BlameableTrait;
    use HasShortFlakePrimary;

    /**
     * Searchable columns for the searchable trait
     *
     * @var array
     */
    protected $searchableColumns = [
        'name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'client_type',
        'identifier',
        'default_duration',
    ];
}
