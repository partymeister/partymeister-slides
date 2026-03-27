<?php

namespace Partymeister\Slides\Http\Controllers\Backend;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Motor\Media\Models\File;
use Motor\Media\Services\FileService;
use Partymeister\Slides\Grids\FileGrid;

/**
 * Class FilesController
 */
class FilesController extends \Motor\Media\Http\Controllers\Backend\FilesController
{
    use FormBuilderTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     *
     * @throws \ReflectionException
     */
    public function index()
    {
        $grid = new FileGrid(File::class);

        $service = FileService::collection($grid);
        $grid->setFilter($service->getFilter());
        $paginator = $service->getPaginator();

        return view('motor-media::backend.files.index', compact('paginator', 'grid'));
    }
}
