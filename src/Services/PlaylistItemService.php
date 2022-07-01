<?php

namespace Partymeister\Slides\Services;

use Motor\Backend\Services\BaseService;
use Partymeister\Slides\Models\PlaylistItem;

/**
 * Class PlaylistItemService
 */
class PlaylistItemService extends BaseService
{
    /**
     * @var string
     */
    protected $model = PlaylistItem::class;
}
