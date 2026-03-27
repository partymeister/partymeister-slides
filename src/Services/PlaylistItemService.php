<?php

namespace Partymeister\Slides\Services;

use Motor\Admin\Services\BaseService;
use Partymeister\Slides\Models\PlaylistItem;

/**
 * Class PlaylistItemService
 */
class PlaylistItemService extends BaseService
{
    protected string $model = PlaylistItem::class;
}
