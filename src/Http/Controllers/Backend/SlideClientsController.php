<?php

namespace Partymeister\Slides\Http\Controllers\Backend;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Motor\Backend\Http\Controllers\Controller;
use Partymeister\Slides\Forms\Backend\SlideClientForm;
use Partymeister\Slides\Grids\SlideClientGrid;
use Partymeister\Slides\Http\Requests\Backend\SlideClientRequest;
use Partymeister\Slides\Models\SlideClient;
use Partymeister\Slides\Services\SlideClientService;

/**
 * Class SlideClientsController
 * @package Partymeister\Slides\Http\Controllers\Backend
 */
class SlideClientsController extends Controller
{
    use FormBuilderTrait;


    /**
     * @param SlideClient        $record
     * @return RedirectResponse|Redirector
     */
    public function activate(Request $request, SlideClient $record)
    {
        session([ 'screens.active' => $record->id ]);

        return redirect($request->server('HTTP_REFERER'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \ReflectionException
     */
    public function index()
    {
        $grid = new SlideClientGrid(SlideClient::class);

        $service = SlideClientService::collection($grid);
        $grid->setFilter($service->getFilter());
        $paginator = $service->getPaginator();

        return view('partymeister-slides::backend.slide_clients.index', compact('paginator', 'grid'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $form = $this->form(SlideClientForm::class, [
            'method'  => 'POST',
            'route'   => 'backend.slide_clients.store',
            'enctype' => 'multipart/form-data'
        ]);

        $motorShowRightSidebar = true;

        return view('partymeister-slides::backend.slide_clients.create', compact('form', 'motorShowRightSidebar'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param SlideClientRequest $request
     * @return RedirectResponse|Redirector
     */
    public function store(SlideClientRequest $request)
    {
        $form = $this->form(SlideClientForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        SlideClientService::createWithForm($request, $form);

        flash()->success(trans('partymeister-slides::backend/slide_clients.created'));

        return redirect('backend/slide_clients');
    }


    /**
     * Display the specified resource.
     *
     * @param $id
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param SlideClient $record
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(SlideClient $record)
    {
        $form = $this->form(SlideClientForm::class, [
            'method'  => 'PATCH',
            'url'     => route('backend.slide_clients.update', [ $record->id ]),
            'enctype' => 'multipart/form-data',
            'model'   => $record
        ]);

        $motorShowRightSidebar = true;

        return view('partymeister-slides::backend.slide_clients.edit', compact('form', 'motorShowRightSidebar'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param SlideClientRequest $request
     * @param SlideClient        $record
     * @return RedirectResponse|Redirector
     */
    public function update(SlideClientRequest $request, SlideClient $record)
    {
        $form = $this->form(SlideClientForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        SlideClientService::updateWithForm($record, $request, $form);

        flash()->success(trans('partymeister-slides::backend/slide_clients.updated'));

        return redirect('backend/slide_clients');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param SlideClient $record
     * @return RedirectResponse|Redirector
     */
    public function destroy(SlideClient $record)
    {
        SlideClientService::delete($record);

        flash()->success(trans('partymeister-slides::backend/slide_clients.deleted'));

        return redirect('backend/slide_clients');
    }
}
