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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Motor\Backend\Models\Category;
use Motor\Backend\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Partymeister\Slides\Models\Slide
 *
 * @property int $id
 * @property int|null $slide_template_id
 * @property string $name
 * @property string $slide_type
 * @property int|null $category_id
 * @property mixed $definitions
 * @property string $cached_html_final
 * @property string $cached_html_preview
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property-read Category|null $category
 * @property-read User $creator
 * @property-read User|null $eraser
 * @property-read Collection|Media[] $media
 * @property-read SlideTemplate $template
 * @property-read User $updater
 *
 * @method static Builder|Slide filteredBy(Filter $filter, $column)
 * @method static Builder|Slide filteredByMultiple(Filter $filter)
 * @method static Builder|Slide newModelQuery()
 * @method static Builder|Slide newQuery()
 * @method static Builder|Slide query()
 * @method static Builder|Slide search($q, $full_text = false)
 * @method static Builder|Slide whereCachedHtmlFinal($value)
 * @method static Builder|Slide whereCachedHtmlPreview($value)
 * @method static Builder|Slide whereCategoryId($value)
 * @method static Builder|Slide whereCreatedAt($value)
 * @method static Builder|Slide whereCreatedBy($value)
 * @method static Builder|Slide whereDefinitions($value)
 * @method static Builder|Slide whereDeletedBy($value)
 * @method static Builder|Slide whereId($value)
 * @method static Builder|Slide whereName($value)
 * @method static Builder|Slide whereSlideTemplateId($value)
 * @method static Builder|Slide whereSlideType($value)
 * @method static Builder|Slide whereUpdatedAt($value)
 * @method static Builder|Slide whereUpdatedBy($value)
 * @mixin Eloquent
 */
class Slide extends Model implements HasMedia
{
    use Searchable;
    use Filterable;
    use Blameable, CreatedBy, UpdatedBy, DeletedBy;
    use InteractsWithMedia;

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
        'slides.name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slide_template_id',
        'slide_type',
        'category_id',
        'definitions',
        'cached_html_preview',
        'cached_html_final',
    ];

    ///**
    // * The attributes that should be cast to native types.
    // *
    // * @var array
    // */
    //protected $casts = [
    //    'definitions' => 'array',
    //];

    /**
     * @param  Media|null  $media
     */
    /**
     * @param  Media|null  $media
     */
    public function registerMediaConversions(Media $media = null): void
    {
        try {
            $this->addMediaConversion('thumb')
                 ->width(400)
                 ->height(400)
                 ->nonQueued();
            $this->addMediaConversion('preview')
                 ->width(400)
                 ->height(400)
                 ->format('png')
                 ->nonQueued();
        } catch (InvalidManipulation $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @return BelongsTo
     */
    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    /**
     * @return BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(SlideTemplate::class);
    }
}
