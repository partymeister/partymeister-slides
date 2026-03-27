<?php

namespace Partymeister\Slides\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Motor\Admin\Models\User;
use Partymeister\Slides\Models\SlideClient;

/**
 * Class SlidemeisterWebController
 */
class SlidemeisterWebController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     *
     * @throws \ReflectionException
     */
    public function index(SlideClient $record)
    {
        // FIXME: this needs to be improved
        // Master api_token
        $apiToken = User::first()->api_token;

        $template = config('partymeister-slides.slidemeister_web_template', 'index');

        return view('partymeister-slides::slidemeister-web/'.$template, ['api_token' => $apiToken]);
    }
}
