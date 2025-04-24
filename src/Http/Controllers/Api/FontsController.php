<?php

namespace Partymeister\Slides\Http\Controllers\Api;

use Motor\Backend\Http\Controllers\ApiController;
use Partymeister\Slides\Http\Resources\FontCollection;

/**
 * Class FontsController
 */
class FontsController extends ApiController
{
    /**
     * @OA\Get (
     *   tags={"FontsController"},
     *   path="/api/fonts",
     *   summary="Get font collection",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *
     *     @OA\JsonContent(
     *
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *
     *         @OA\Items(ref="#/components/schemas/FontResource")
     *       ),
     *
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Collection read"
     *       )
     *     )
     *   )
     * )
     *
     * Display a listing of the resource.
     *
     * @return FontCollection
     */
    public function index()
    {
        $fonts = config('partymeister-slides-fonts.fonts');

        return (new FontCollection($fonts))->additional(['message' => 'Font collection read']);
    }
}
