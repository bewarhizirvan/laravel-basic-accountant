<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Currency;
use App\Models\Safe;
use App\Models\Wallet;
use BewarHizirvan\LaravelForm\LaravelForm;
use BewarHizirvan\LaravelGrid\LaravelGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Wallets';
        $model = Wallet::query();
        $buttons =  "<button
            class='btn btn-primary btn-sm mb-0'
            style='border-radius:20px;'
            onclick=\"location.href='".route('wallet.create')."'\">
            Add Wallet
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'deposit')."'\">
            Add Deposit
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'withdraw')."'\">
            Add Withdraw
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.exchange.create', 'wallet_exchange')."'\">
            Add Exchange
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.transfer.create', 'wallet_transfer')."'\">
            Add Transfer
            </button>";

        $parameters = [
            'label' =>$buttons,
            'checkClass' => null,
            'paginate' => 50,
            'provider' => $model,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->addColumn('name', 'Name', true);
        $grid->addColumn('description', 'Description', true);
        $grid->addColumn('address', 'Address', true);
        $grid->addColumn('currency.code', 'Currency');
        $grid->addColumn('active', 'Active', false, false, null, function ($val) {
            return ($val) ? LaravelGrid::ENABLED : LaravelGrid::DISABLED;
        });
        $grid->addColumn('user.name', 'AddedBy');
        $grid->addColumn('created_at', 'Added DateTime');
        $grid->addColumn('updated_at', 'Updated');
        $grid->addActionColumn('id');
        $grid->addActionButton('account', 'Show', 'wallet.show');
        $grid->addActionButton('edit', 'Edit', 'wallet.edit');
        //$grid->addActionButton('remove', 'Remove', 'wallet.destroy');

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
            'title' => 'Add Wallet',
            'route' => 'wallet.store',
            'class' => 'form-horizontal'
        ];
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $form = new LaravelForm($parameters);
        $form->addText('name', '', ['required' => 1], 'Name', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('description', '', [], 'Description', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('address', '', [], 'Address', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
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
            'name' => ['required'],
        ])->validate();

        $model = new Wallet;
        $model->name = $request->input('name');
        $model->description = $request->input('description');
        $model->address = $request->input('address');
        $model->currency_id = $request->input('currency_id');
        $model->active = $request->has('active');
        $model->user_id = $this->user->getAuthIdentifier();
        $model->save();

        return redirect()->route('wallet.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $wallet = Wallet::find($id);
        $title = "Balances of Wallet - ". $wallet->name;
        $balances_in = Safe::selectRaw('`currency_id`, sum(`amount`) as amount')
            ->where('wallet_id', $id)
            ->where('active',1)
            ->where('direction','in')
            ->groupby('currency_id')->get();
        $balances_out = Safe::selectRaw('`currency_id`, sum(`amount`) as amount')
            ->where('wallet_id', $id)
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
            $balances[$item->currency_id] = ($balances[$item->currency_id] ?? 0) - $item->amount;
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
        $grid .= SafeController::indexGrid(null, ['wallet_id' => $id]);
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
            'title' => 'Edit Wallet',
            'route' => ['wallet.update', $id],
            'method' => 'put',
            'class' => 'form-horizontal'
        ];
        $model = Wallet::find($id);
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $form = new LaravelForm($parameters);
        $form->addText('name', $model->name, ['required' => 1], 'Name', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('description', $model->description, [], 'Description', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('address', $model->address, [], 'Address', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
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
            'name' => ['required'],
        ])->validate();

        $model = Wallet::find($id);
        $model->name = $request->input('name');
        $model->description = $request->input('description');
        $model->address = $request->input('address');
        $model->currency_id = $request->input('currency_id');
        $model->active = $request->has('active');
        $model->save();

        return redirect()->route('wallet.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return int
     */
    public function destroy($id)
    {
        Wallet::destroy($id);

        return 1;
    }
}
