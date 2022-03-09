<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Safe;
use BewarHizirvan\LaravelForm\LaravelForm;
use BewarHizirvan\LaravelGrid\LaravelGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Customers';
        $model = Customer::query();
        $buttons = "<button
            class='btn btn-primary btn-sm mb-0'
            style='border-radius:20px;'
            onclick=\"location.href='".route('customer.create')."'\">
            Add Customer
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'debit')."'\">
            Add Debit
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'credit')."'\">
            Add Credit
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.exchange.create', 'customer_exchange')."'\">
            Add Exchange
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.transfer.create', 'customer_transfer')."'\">
            Add Transfer
            </button>";

        $parameters = [
            'label' => $buttons,
            'checkClass' => null,
            'paginate' => 50,
            'provider' => $model,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->addColumn('full_name', 'Full Name', true);
        $grid->addColumn('address', 'Address', true);
        $grid->addColumn('phone', 'Phone', true);
        $grid->addColumn('email', 'Email', true);
        $grid->addColumn('currency.code', 'Currency');
        $grid->addColumn('active', 'Active', false, false, null, function ($val) {
            return ($val) ? LaravelGrid::ENABLED : LaravelGrid::DISABLED;
        });
        $grid->addColumn('user.name', 'AddedBy');
        $grid->addColumn('created_at', 'Added DateTime');
        $grid->addActionColumn('id');
        $grid->addActionButton('account', 'Show', 'customer.show');
        $grid->addActionButton('edit', 'Edit', 'customer.edit');
        //$grid->addActionButton('remove', 'Remove', 'customer.destroy');

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
            'title' => 'Add Customer',
            'route' => 'customer.store',
            'class' => 'form-horizontal'
        ];
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $form = new LaravelForm($parameters);
        $form->addText('full_name', '', ['required' => 1], 'Full Name', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('address', '', ['required' => 1], 'Address', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('phone', '', ['required' => 1], 'Phone', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('email', '', [], 'Email', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addSelect('currency_id', $currencies, 1, [], 'Currency');
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
            'full_name' => ['required'],
            'address' => ['required'],
            'phone' => ['required'],
        ])->validate();

        $model = new Customer;
        $model->full_name = $request->input('full_name');
        $model->address = $request->input('address');
        $model->phone = $request->input('phone');
        $model->email = $request->input('email');
        $model->currency_id = $request->input('currency_id');
        $model->active = $request->has('active');
        $model->user_id = $this->user->getAuthIdentifier();
        $model->save();

        return redirect()->route('customer.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = Customer::find($id);
        $title = "Balances of Customer - ". $customer->full_name ;
        $balances_in = Safe::selectRaw('`currency_id`, sum(`amount`) as amount')
            ->where('customer_id', $id)
            ->where('active',1)
            ->where('direction','in')
            ->groupby('currency_id')->get();
        $balances_out = Safe::selectRaw('`currency_id`, sum(`amount`) as amount')
            ->where('customer_id', $id)
            ->where('active',1)
            ->where('direction','out')
            ->groupby('currency_id')->get();
        $balances = [];
        foreach ($balances_in as $item)
        {
            $balances[$item->currency_id] = $item->amount;
        }
        foreach ($balances_out as $item)
        {
            $balances[$item->currency_id] = ($balances[$item->currency_id   ] ?? 0) - $item->amount;
        }
        //print_r($balances);
        //return '';
        $model = Currency::wherein('id', array_keys($balances));
        $parameters = [
            'checkClass' => null,
            'paginate' => 50,
            'provider' => $model,
            'headerCounter' => false,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->addColumn('code', 'Code');
        $grid->addColumn('balance', 'Balance', false, false, function($row) use($balances) {
            if($balances[$row->id] >= 0)
                $color = "ForestGreen";
            else
                $color = "LightCoral";
            return '<span style="color:'.$color.'">'.FormatHelper::CurrencyFormat($balances[$row->id]).'</span>';
        });
        $grid = $grid->render();
        $grid .= "<div class='py-2'></div>";
        $grid .= SafeController::indexGrid(null, ['customer_id' => $id]);
        return response()->view('body', compact('grid', 'title'));
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
            'title' => 'Edit Customer',
            'route' => ['customer.update', $id],
            'method' => 'put',
            'class' => 'form-horizontal'
        ];
        $model = Customer::find($id);
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $form = new LaravelForm($parameters);
        $form->addText('full_name', $model->full_name, ['required' => 1], 'Full Name', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('address', $model->address, ['required' => 1], 'Address', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('phone', $model->phone, ['required' => 1], 'Phone', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('email', $model->email, [], 'email', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addSelect('currency_id', $currencies, $model->currency_id, [], 'Currency');
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
            'full_name' => ['required'],
            'address' => ['required'],
            'phone' => ['required'],
        ])->validate();

        $model = Customer::find($id);
        $model->full_name = $request->input('full_name');
        $model->address = $request->input('address');
        $model->phone = $request->input('phone');
        $model->email = $request->input('email');
        $model->currency_id = $request->input('currency_id');
        $model->active = $request->has('active');
        $model->save();

        return redirect()->route('customer.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return int
     */
    public function destroy($id)
    {
        Customer::destroy($id);

        return 1;
    }
}
