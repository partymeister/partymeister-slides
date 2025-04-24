<?php

namespace Partymeister\Slides\Http\Controllers\Backend;

use Illuminate\Http\Response;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Motor\Backend\Http\Controllers\Controller;
use Partymeister\Slides\Forms\Backend\TransitionForm;
use Partymeister\Slides\Grids\TransitionGrid;
use Partymeister\Slides\Http\Requests\Backend\TransitionRequest;
use Partymeister\Slides\Models\Transition;
use Partymeister\Slides\Services\TransitionService;

/**
 * Class TransitionsController
 */
class TransitionsController extends Controller
{
    use FormBuilderTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \ReflectionException
     */
    public function index()
    {
        $grid = new TransitionGrid(Transition::class);

        $service = TransitionService::collection($grid);
        $grid->setFilter($service->getFilter());
        $paginator = $service->getPaginator();

        return view('partymeister-slides::backend.transitions.index', compact('paginator', 'grid'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $form = $this->form(TransitionForm::class, [
            'method' => 'POST',
            'route' => 'backend.transitions.store',
            'enctype' => 'multipart/form-data',
        ]);

        return view('partymeister-slides::backend.transitions.create', compact('form'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(TransitionRequest $request)
    {
        $form = $this->form(TransitionForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()
                ->back()
                ->withErrors($form->getErrors())
                ->withInput();
        }

        TransitionService::createWithForm($request, $form);

        flash()->success(trans('partymeister-slides::backend/transitions.created'));

        return redirect('backend/transitions');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Transition $record)
    {
        $form = $this->form(TransitionForm::class, [
            'method' => 'PATCH',
            'url' => route('backend.transitions.update', [$record->id]),
            'enctype' => 'multipart/form-data',
            'model' => $record,
        ]);

        return view('partymeister-slides::backend.transitions.edit', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(TransitionRequest $request, Transition $record)
    {
        $form = $this->form(TransitionForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()
                ->back()
                ->withErrors($form->getErrors())
                ->withInput();
        }

        TransitionService::updateWithForm($record, $request, $form);

        flash()->success(trans('partymeister-slides::backend/transitions.updated'));

        return redirect('backend/transitions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Transition $record)
    {
        TransitionService::delete($record);

        flash()->success(trans('partymeister-slides::backend/transitions.deleted'));

        return redirect('backend/transitions');
    }
}
