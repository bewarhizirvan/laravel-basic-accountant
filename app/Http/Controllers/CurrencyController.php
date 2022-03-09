<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use BewarHizirvan\LaravelForm\LaravelForm;
use BewarHizirvan\LaravelGrid\LaravelGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Currencies';
        $model = Currency::query();
        $parameters = [
            'label' => "<button
            class='btn btn-primary btn-sm mb-0'
            style='border-radius:20px;'
            onclick=\"location.href='".route('currency.create')."'\">
            Add Currency
            </button>",
            'checkClass' => null,
            'paginate' => 50,
            'provider' => $model,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->addColumn('code', 'Code');
        $grid->addColumn('rate', 'Rate');
        $grid->addColumn('active', 'Active', false, false, null, function ($val) {
            return ($val) ? LaravelGrid::ENABLED : LaravelGrid::DISABLED;
        });
        $grid->addColumn('user.name', 'AddedBy');
        $grid->addColumn('created_at', 'Added DateTime');
        $grid->addActionColumn('id');
        $grid->addActionButton('edit', 'Edit', 'currency.edit');
        //$grid->addActionButton('remove', 'Remove', 'currency.destroy');

        $grid = $grid->render();
        return response()->view('body', compact('grid', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $parameters = [
            'title' => 'Add Currency',
            'route' => 'currency.store',
            'class' => 'form-horizontal'
        ];

        $form = new LaravelForm($parameters);
        $form->addText('code', '', ['required' => 1], 'Code', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('rate', '1', ['required' => 1], 'Rate', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addCheckbox('active', '1', true, ['class' => 'form-checkbox form-check-input'], 'Active', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);

        $form = $form->render();

        return response()->view('body', compact('form'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'code' => ['required'],
            'rate' => ['required'],
        ])->validate();

        $model = new Currency;
        $model->code = $request->input('code');
        $model->rate = $request->input('rate');
        $model->active = $request->has('active');
        $model->user_id = $this->user->getAuthIdentifier();
        $model->save();

        return redirect()->route('currency.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $parameters = [
            'title' => 'Add Currency',
            'route' => ['currency.update',$id],
            'method' => 'put',
            'class' => 'form-horizontal'
        ];

        $model = Currency::find($id);
        $form = new LaravelForm($parameters);
        $form->addText('code', $model->code, ['required' => 1], 'Code', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('rate', $model->rate, ['required' => 1], 'Rate', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addCheckbox('active', '1', $model->active, ['class' => 'form-checkbox form-check-input'], 'Active', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);

        $form = $form->render();

        return response()->view('body', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'code' => ['required'],
            'rate' => ['required'],
        ])->validate();

        $model = Currency::find($id);
        $model->code = $request->input('code');
        $model->rate = $request->input('rate');
        $model->active = $request->has('active');
        $model->save();

        return redirect()->route('currency.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return int
     */
    public function destroy($id)
    {
        Currency::destroy($id);

        return 1;
    }
}
