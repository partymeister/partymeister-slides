<?php

namespace Partymeister\Slides\Http\Controllers\Api\V2;

use Illuminate\Routing\Controller;
use Partymeister\Slides\Http\Resources\V2\FontCollection;

/**
 * @tags Slides: Fonts
 */
class FontsController extends Controller
{
    /**
     * @response FontCollection
     */
    public function index(): FontCollection
    {
        $fonts = config('partymeister-slides-fonts.fonts', []);

        return (new FontCollection(collect($fonts)))
            ->additional(['meta' => ['message' => 'Fonts retrieved']]);
    }
}
