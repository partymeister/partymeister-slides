<?php

namespace Partymeister\Slides\Models;

use Culpa\Traits\Blameable;
use Culpa\Traits\CreatedBy;
use Culpa\Traits\DeletedBy;
use Culpa\Traits\UpdatedBy;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Motor\Backend\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Partymeister\Slides\Models\SlideTemplate
 *
 * @property int $id
 * @property string $name
 * @property string $template_for
 * @property mixed $definitions
 * @property string $cached_html_final
 * @property string $cached_html_preview
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read User|null $eraser
 * @property-read Collection|Media[] $media
 * @property-read Collection|Slide[] $slides
 * @property-read User $updater
 *
 * @method static Builder|SlideTemplate filteredBy(Filter $filter, $column)
 * @method static Builder|SlideTemplate filteredByMultiple(Filter $filter)
 * @method static Builder|SlideTemplate newModelQuery()
 * @method static Builder|SlideTemplate newQuery()
 * @method static Builder|SlideTemplate query()
 * @method static Builder|SlideTemplate search($q, $full_text = false)
 * @method static Builder|SlideTemplate whereCachedHtmlFinal($value)
 * @method static Builder|SlideTemplate whereCachedHtmlPreview($value)
 * @method static Builder|SlideTemplate whereCreatedAt($value)
 * @method static Builder|SlideTemplate whereCreatedBy($value)
 * @method static Builder|SlideTemplate whereDefinitions($value)
 * @method static Builder|SlideTemplate whereDeletedBy($value)
 * @method static Builder|SlideTemplate whereId($value)
 * @method static Builder|SlideTemplate whereName($value)
 * @method static Builder|SlideTemplate whereTemplateFor($value)
 * @method static Builder|SlideTemplate whereUpdatedAt($value)
 * @method static Builder|SlideTemplate whereUpdatedBy($value)
 * @mixin Eloquent
 */
class SlideTemplate extends Model implements HasMedia
{
    use Searchable;
    use Filterable;
    use Blameable, CreatedBy, UpdatedBy, DeletedBy;
    use InteractsWithMedia;

    /**
     * @param  Media|null  $media
     *
     * @throws InvalidManipulation
     */
    /**
     * Columns for the Blameable trait
     *
     * @var array
     */
    protected $blameable = ['created', 'updated', 'deleted'];

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
        'template_for',
        'definitions',
        'cached_html_preview',
        'cached_html_final',
    ];

    /**
     * @param  Media|null  $media
     *
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(400)
             ->height(400)
             ->nonQueued();
        $this->addMediaConversion('preview')
             ->width(400)
             ->height(400)
             ->format('png')
             ->nonQueued();
    }

    ///**
    // * The attributes that should be cast to native types.
    // *
    // * @var array
    // */
    //protected $casts = [
    //    'definitions' => 'array',
    //];

    /**
     * @return HasMany
     */

    /**
     * @return HasMany
     */
    public function slides()
    {
        return $this->hasMany(Slide::class);
    }
}
