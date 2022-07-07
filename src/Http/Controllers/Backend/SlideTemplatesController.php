<?php

namespace Partymeister\Slides\Http\Controllers\Backend;

use Kris\LaravelFormBuilder\FormBuilderTrait;
use Motor\Admin\Http\Controllers\Controller;
use Partymeister\Slides\Forms\Backend\SlideTemplateForm;
use Partymeister\Slides\Grids\SlideTemplateGrid;
use Partymeister\Slides\Http\Requests\Backend\SlideTemplateRequest;
use Partymeister\Slides\Models\SlideTemplate;
use Partymeister\Slides\Services\SlideTemplateService;

/**
 * Class SlideTemplatesController
 */
class SlideTemplatesController extends Controller
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
        $grid = new SlideTemplateGrid(SlideTemplate::class);

        $service = SlideTemplateService::collection($grid);
        $grid->setFilter($service->getFilter());
        $paginator = $service->getPaginator();

        return view('partymeister-slides::backend.slide_templates.index', compact('paginator', 'grid'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SlideTemplateRequest  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(SlideTemplateRequest $request)
    {
        $form = $this->form(SlideTemplateForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()
                ->back()
                ->withErrors($form->getErrors())
                ->withInput();
        }

        SlideTemplateService::createWithForm($request, $form);

        flash()->success(trans('partymeister-slides::backend/slide_templates.created'));

        return redirect('backend/slide_templates');
    }

    /**
     * @param  SlideTemplate  $record
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function duplicate(SlideTemplate $record)
    {
        $newRecord = $record->replicate();
        $newRecord->name = 'Duplicate of '.$newRecord->name;

        return $this->create($newRecord);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  SlideTemplate  $record
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(SlideTemplate $record)
    {
        $form = $this->form(SlideTemplateForm::class, [
            'method'  => 'POST',
            'route'   => 'backend.slide_templates.store',
            'enctype' => 'multipart/form-data',
            'model'   => $record,
        ]);

        $motorShowRightSidebar = true;

        return view('partymeister-slides::backend.slide_templates.create', compact('form', 'motorShowRightSidebar'));
    }

    /**
     * Display the specified resource.
     *
     * @param  SlideTemplateRequest  $request
     * @param  SlideTemplate  $record
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(SlideTemplateRequest $request, SlideTemplate $record)
    {
        $preview = $request->get('preview', 'false');

        $placeholderData = json_encode([]);

        return view('partymeister-slides::backend.slide_templates.show', compact('record', 'preview', 'placeholderData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  SlideTemplate  $record
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(SlideTemplate $record)
    {
        $form = $this->form(SlideTemplateForm::class, [
            'method'  => 'PATCH',
            'url'     => route('backend.slide_templates.update', [$record->id]),
            'enctype' => 'multipart/form-data',
            'model'   => $record,
        ]);

        $motorShowRightSidebar = true;

        return view('partymeister-slides::backend.slide_templates.edit', compact('form', 'motorShowRightSidebar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SlideTemplateRequest  $request
     * @param  SlideTemplate  $record
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(SlideTemplateRequest $request, SlideTemplate $record)
    {
        $form = $this->form(SlideTemplateForm::class);

        // It will automatically use current request, get the rules, and do the validation
        if (! $form->isValid()) {
            return redirect()
                ->back()
                ->withErrors($form->getErrors())
                ->withInput();
        }

        SlideTemplateService::updateWithForm($record, $request, $form);

        flash()->success(trans('partymeister-slides::backend/slide_templates.updated'));

        return redirect('backend/slide_templates');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  SlideTemplate  $record
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(SlideTemplate $record)
    {
        SlideTemplateService::delete($record);

        flash()->success(trans('partymeister-slides::backend/slide_templates.deleted'));

        return redirect('backend/slide_templates');
    }
}
