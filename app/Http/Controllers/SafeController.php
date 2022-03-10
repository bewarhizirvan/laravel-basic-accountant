<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Currency;
use App\Models\Safe;
use App\Models\Wallet;
use App\Policies\CheckPerm;
use BewarHizirvan\LaravelForm\LaravelForm;
use BewarHizirvan\LaravelGrid\LaravelGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SafeController extends BaseController
{
    public static function indexGrid($buttons = null, $filterArray = [])
    {
        $model = Safe::query();
        if(isset($filterArray['wallet_id'])) $model->where('wallet_id', $filterArray['wallet_id']);
        if(isset($filterArray['customer_id'])) $model->where('customer_id', $filterArray['customer_id']);
        if(isset($filterArray['user_id'])) $model->where('user_id', $filterArray['user_id']);
        $parameters = [
            'label' => $buttons,
            'checkClass' => new CheckPerm,
            'paginate' => 50,
            'provider' => $model,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->orderBy('created_at', 'desc');
        $grid->addColumn('full_name', 'Full Name', true);
        $grid->addColumn('description', 'Description', true);
        $grid->addColumn('address', 'Address', true);
        $grid->addFilterSelect('type', 'Type', FormatHelper::SafeType());
        $grid->addColumn('type', 'Type', false, false, null, function ($val) {
            return FormatHelper::SafeType($val);
        });
        //$grid->addColumn('direction', 'Direction');
        $grid->addColumn('amount', 'Amount', false, false, function ($row) {
            $sign = "";
            $color = "ForestGreen";
            if($row->direction == 'out')
            {
                $sign = "-";
                $color = "LightCoral";
            }
            return '<span style="color:'.$color.'">'
                .$sign.FormatHelper::CurrencyFormat($row->amount) .' '. $row->currency->code
                .'</span>';
        });
        $grid->addColumn('wallet.name', 'Wallet');
        $grid->addColumn('active', 'Active', false, false, null, function ($val) {
            return ($val) ? LaravelGrid::OK : LaravelGrid::NOTOK;
        });
        $grid->addColumn('user.name', 'AddedBy');
        $grid->addColumn('created_at', 'DateTime');
        $grid->addColumn('updated_at', 'Updated');
        $grid->addActionColumn('id');
        $grid->addActionButton('edit', 'Edit', 'safe.edit');
        $grid->addActionButton('remove', 'Remove', 'safe.destroy', [['name' => 'perm', 'value' => 'destroy_file']]);
        return $grid->render();
    }

    public function create($type)
    {
        switch ($type)
        {
            case 'spend':
                $title = 'Add Spend Record';
                $direction = 'out';
                break;

            case 'cash_in':
                $title = 'Add Cash-In Record';
                $direction = 'in';
                break;

            case 'cash_out':
                $title = 'Add Cash-Out Record';
                $direction = 'out';
                break;

            case 'debit':
                $title = 'Add Debit Record';
                $direction = 'out';
                break;

            case 'credit':
                $title = 'Add Credit Record';
                $direction = 'in';
                break;

            case 'deposit':
                $title = 'Add Deposit Record';
                $direction = 'in';
                break;

            case 'withdraw':
                $title = 'Add Withdraw Record';
                $direction = 'out';
                break;

            default:
                $title = 'Add Safe Record';
                $direction = 'out';
        }
        $parameters = [
            'title' => $title,
            'route' => 'safe.store',
            'class' => 'form-horizontal'
        ];
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $wallets = Wallet::where('active', 1)->pluck('name', 'id');
        $form = new LaravelForm($parameters);
        $form->addHidden('type', $type);
        $form->addHidden('direction', $direction);
        if(!in_array($type, ['deposit', 'withdraw']))
        {
            $form->addText('full_name', '', ['autocomplete' => 'off'], 'Customer / Full Name', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
            $form->addHidden('customer_id', '', ['id' => 'customer_id']);
        }
        $form->addText('description', '', [], 'Description', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addText('address', '', [], 'Address', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addText('amount', '', ['required' => 1], 'Amount', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addSelect('currency_id', $currencies, 1, [], 'Currency');
        if(!in_array($type, ['debit']))
        {
            $form->addSelect('wallet_id', $wallets, 1, [], 'Wallet');
        }
        $form->addText('created_at', date('Y-m-d\TH:i:s', strtotime('now')), ['type' => 'datetime-local'], 'DateTime', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form = $form->render();

        $script = '<link href="'.asset('css/jquery-ui.min.css').'" rel="stylesheet"/>
<script src="'.asset('js/jquery-ui.min.js?v=2.0.0').'"></script>
<script type="application/javascript">
$("#full_name").autocomplete({
    source: function (request, response) {
        //console.log(getRootUrl());
        $(document.formProductionSale).find(":submit").prop("disabled", true);
        jQuery.get("'.route('ajax.customer').'", {full_name: request.term}, function (data) {
            response(data);
        });
    },
    autoFocus: true,
    select: function (event, ui) {
        $("#customer_id").val(ui.item.customer_id);
        $("#description").focus();
    },

    minLength: 1
});
$("#amount").keyup(function (e) {
    var $this = $(this);
    var num = $this.val().replace(/,/gi, "");
    var num2 = num.replace(/\d(?=(?:\d{3})+$)/g, \'$&,\');
    $this.val(num2);

})
</script>';
        return response()->view('body', compact('form', 'script'));
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
            'amount' => ['required'],
            'type' => ['required'],
            'direction' => ['required'],
        ])->validate();

        $model = new Safe;
        $model->full_name = $request->input('full_name');
        $model->address = $request->input('address');
        $model->description = $request->input('description');
        $model->type = $request->input('type');
        $model->direction = $request->input('direction');
        $model->amount = floatval(Str::replace(",","",$request->input('amount')));
        $model->currency_id = $request->input('currency_id');
        $model->wallet_id = $request->input('wallet_id') != '' ? $request->input('wallet_id') : 0;
        $model->customer_id = $request->input('customer_id') != '' ? $request->input('customer_id') : 0;
        $model->user_id = $this->user->getAuthIdentifier();
        $model->created_at = $request->input('created_at');
        $model->save();

        return redirect()->route('home');
    }

    public function createExchange($type)
    {
        switch ($type)
        {
            case 'customer_exchange':
                $title = 'Add Customer Exchange Record';
                break;

            case 'wallet_exchange':
                $title = 'Add Wallet Exchange Record';
                break;

            default:
                $title = 'Add Exchange Record';
        }
        $parameters = [
            'title' => $title,
            'route' => 'safe.exchange.store',
            'class' => 'form-horizontal'
        ];
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $select_options = '';
        foreach (Currency::where('active', 1)->get() as $currency)
        {
            $select_options .=
                '<option value="'.$currency->id.'"'.($currency->id == "1" ? " selected":"").'>
                '.$currency->code.'
                </option>';
        }
        $wallets = Wallet::where('active', 1)->pluck('name', 'id');
        $form = new LaravelForm($parameters);
        $form->addHidden('type', $type);
        if(!in_array($type, ['wallet_exchange']))
        {
            $form->addText('full_name', '', ['autocomplete' => 'off'], 'Customer / Full Name', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
            $form->addHidden('customer_id', '', ['id' => 'customer_id']);
        }
        if(!in_array($type, ['customer_exchange']))
        {
            $form->addSelect('wallet_id', $wallets, 1, [], 'Wallet');
        }
        $form->addText('description', '', [], 'Description', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $from_amount_html = '<div class="input-group mb-3">
  <input name="amount_out" id="amount_out" type="text" class="form-control" placeholder="Amount" aria-label="Amount">
  <select class="form-control" type="select" id="currency_id_out" name="currency_id_out">
                            '.$select_options.'
                        </select>
</div>';
        $form->addHtml($from_amount_html, 'Amount-Out', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $to_amount_html = '<div class="input-group mb-3">
  <input name="amount_in" id="amount_in" type="text" class="form-control" placeholder="Amount" aria-label="Amount">
  <select class="form-control" type="select" id="currency_id_in" name="currency_id_in">
                            '.$select_options.'
                        </select>
</div>';
        $form->addHtml($to_amount_html, 'Amount-In', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);


        $form = $form->render();

        $script = '<link href="'.asset('css/jquery-ui.min.css').'" rel="stylesheet"/>
<script src="'.asset('js/jquery-ui.min.js?v=2.0.0').'"></script>
<script type="application/javascript">
$("#full_name").autocomplete({
    source: function (request, response) {
        //console.log(getRootUrl());
        $(document.formProductionSale).find(":submit").prop("disabled", true);
        jQuery.get("'.route('ajax.customer').'", {full_name: request.term}, function (data) {
            response(data);
        });
    },
    autoFocus: true,
    select: function (event, ui) {
        $("#customer_id").val(ui.item.customer_id);
        $("#description").focus();
    },

    minLength: 1
});
$("#amount_out").keyup(function (e) {
    var $this = $(this);
    var num = $this.val().replace(/,/gi, "");
    var num2 = num.replace(/\d(?=(?:\d{3})+$)/g, \'$&,\');
    $this.val(num2);

});
    $("#amount_in").keyup(function (e) {
    var $this = $(this);
    var num = $this.val().replace(/,/gi, "");
    var num2 = num.replace(/\d(?=(?:\d{3})+$)/g, \'$&,\');
    $this.val(num2);

})
</script>';
        return response()->view('body', compact('form', 'script'));
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeExchange(Request $request)
    {
        Validator::make($request->all(), [
            'amount_out' => ['required'],
            'amount_in' => ['required'],
            'currency_id_out' => ['required'],
            'currency_id_in' => ['required'],
        ])->validate();

        $model_out = new Safe;
        $model_out->full_name = $request->input('full_name');
        $model_out->address = '';
        $model_out->description = $request->input('description');
        $model_out->type = $request->input('type');
        $model_out->direction = 'out';
        $model_out->amount = floatval(Str::replace(",","",$request->input('amount_out')));
        $model_out->currency_id = $request->input('currency_id_out');
        $model_out->wallet_id = $request->input('wallet_id') != '' ? $request->input('wallet_id') : 0;
        $model_out->customer_id = $request->input('customer_id') != '' ? $request->input('customer_id') : 0;
        $model_out->user_id = $this->user->getAuthIdentifier();
        $model_out->save();

        $model_in = new Safe;
        $model_in->full_name = $request->input('full_name');
        $model_in->address = $request->input('address');
        $model_in->description = $request->input('description');
        $model_in->type = $request->input('type');
        $model_in->direction = 'in';
        $model_in->amount = floatval(Str::replace(",","",$request->input('amount_in')));
        $model_in->currency_id = $request->input('currency_id_in');
        $model_in->wallet_id = $request->input('wallet_id') != '' ? $request->input('wallet_id') : 0;
        $model_in->customer_id = $request->input('customer_id') != '' ? $request->input('customer_id') : 0;
        $model_in->user_id = $this->user->getAuthIdentifier();
        $model_in->save();

        return redirect()->route('home');
    }

    public function createTransfer($type)
    {
        switch ($type)
        {
            case 'customer_transfer':
                $title = 'Add Customer Transfer Record';
                break;

            case 'wallet_transfer':
                $title = 'Add Wallet Transfer Record';
                break;

            default:
                $title = 'Add Transfer Record';
        }
        $parameters = [
            'title' => $title,
            'route' => 'safe.transfer.store',
            'class' => 'form-horizontal'
        ];
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $select_options = '';
        foreach (Currency::where('active', 1)->get() as $currency)
        {
            $select_options .=
                '<option value="'.$currency->id.'"'.($currency->id == "1" ? " selected":"").'>
                '.$currency->code.'
                </option>';
        }
        $wallets = Wallet::where('active', 1)->pluck('name', 'id');
        $form = new LaravelForm($parameters);
        $form->addHidden('type', $type);
        if(!in_array($type, ['wallet_transfer']))
        {
            $form->addText('full_name_from', '', ['autocomplete' => 'off'], 'From Customer', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
            $form->addHidden('customer_id_from', '', ['id' => 'customer_id_from']);
            $form->addText('full_name_to', '', ['autocomplete' => 'off'], 'To Customer', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
            $form->addHidden('customer_id_to', '', ['id' => 'customer_id_to']);
        }
        if(!in_array($type, ['customer_transfer']))
        {
            $form->addSelect('wallet_id_from', $wallets, 1, [], 'From Wallet');
            $form->addSelect('wallet_id_to', $wallets, 1, [], 'To Wallet');
        }
        $form->addText('description', '', [], 'Description', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $amount_html = '<div class="input-group mb-3">
  <input name="amount" id="amount" type="text" class="form-control" placeholder="Amount" aria-label="Amount">
  <select class="form-control" type="select" id="currency_id" name="currency_id">
                            '.$select_options.'
                        </select>
</div>';
        $form->addHtml($amount_html, 'Amount', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);


        $form = $form->render();

        $script = '<link href="'.asset('css/jquery-ui.min.css').'" rel="stylesheet"/>
<script src="'.asset('js/jquery-ui.min.js?v=2.0.0').'"></script>
<script type="application/javascript">
$("#full_name_from").autocomplete({
    source: function (request, response) {
        //console.log(getRootUrl());
        $(document.formProductionSale).find(":submit").prop("disabled", true);
        jQuery.get("'.route('ajax.customer').'", {full_name: request.term}, function (data) {
            response(data);
        });
    },
    autoFocus: true,
    select: function (event, ui) {
        $("#customer_id_from").val(ui.item.customer_id);
        $("#full_name_to").focus();
    },

    minLength: 1
});
$("#full_name_to").autocomplete({
    source: function (request, response) {
        //console.log(getRootUrl());
        $(document.formProductionSale).find(":submit").prop("disabled", true);
        jQuery.get("'.route('ajax.customer').'", {full_name: request.term}, function (data) {
            response(data);
        });
    },
    autoFocus: true,
    select: function (event, ui) {
        $("#customer_id_to").val(ui.item.customer_id);
        $("#description").focus();
    },

    minLength: 1
});
$("#amount").keyup(function (e) {
    var $this = $(this);
    var num = $this.val().replace(/,/gi, "");
    var num2 = num.replace(/\d(?=(?:\d{3})+$)/g, \'$&,\');
    $this.val(num2);

});
</script>';
        return response()->view('body', compact('form', 'script'));
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function storeTransfer(Request $request)
    {
        Validator::make($request->all(), [
            'amount' => ['required'],
            'currency_id' => ['required'],
        ])->validate();

        $model_out = new Safe;
        $model_out->full_name = $request->input('full_name_from');
        $model_out->address = '';
        $model_out->description = $request->input('description');
        $model_out->type = $request->input('type');
        $model_out->direction = 'out';
        $model_out->amount = floatval(Str::replace(",","",$request->input('amount')));
        $model_out->currency_id = $request->input('currency_id');
        $model_out->wallet_id = $request->input('wallet_id_from') != '' ? $request->input('wallet_id_from') : 0;
        $model_out->customer_id = $request->input('customer_id_from') != '' ? $request->input('customer_id_from') : 0;
        $model_out->user_id = $this->user->getAuthIdentifier();
        $model_out->save();

        $model_in = new Safe;
        $model_in->full_name = $request->input('full_name_to');
        $model_in->address = $request->input('address');
        $model_in->description = $request->input('description');
        $model_in->type = $request->input('type');
        $model_in->direction = 'in';
        $model_in->amount = floatval(Str::replace(",","",$request->input('amount')));
        $model_in->currency_id = $request->input('currency_id');
        $model_in->wallet_id = $request->input('wallet_id_to') != '' ? $request->input('wallet_id_to') : 0;
        $model_in->customer_id = $request->input('customer_id_to') != '' ? $request->input('customer_id_to') : 0;
        $model_in->user_id = $this->user->getAuthIdentifier();
        $model_in->save();

        return redirect()->route('home');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $title = "Edit Safe Record";
        $parameters = [
            'title' => $title,
            'route' => ['safe.update', $id],
            'method' => 'put',
            'class' => 'form-horizontal'
        ];
        $currencies = Currency::where('active', 1)->pluck('code', 'id');
        $wallets = Wallet::where('active', 1)->pluck('name', 'id');
        $model = Safe::find($id);
        $form = new LaravelForm($parameters);
        $form->addText('full_name', $model->full_name, ['disabled' => '1'], 'Customer / Full Name', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addText('description', $model->description, [], 'Description', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addText('address', $model->address, [], 'Address', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addText('amount', $model->amount, ['required' => 1], 'Amount', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form->addSelect('currency_id', $currencies, $model->currency_id, [], 'Currency');
        $form->addSelect('wallet_id', $wallets, $model->wallet_id, ['disabled' => '1'], 'Wallet');
        $form->addCheckbox('active', '1', $model->active, ['class' => 'form-checkbox form-check-input'], 'Active', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('created_at', date('Y-m-d\TH:i:s', strtotime($model->created_at)), ['type' => 'datetime-local'], 'DateTime', ['class' => 'col-md-3 col-form-label'], ['class' => "row"]);
        $form = $form->render();

        $script = '<script type="application/javascript">
$("#amount").keyup(function (e) {
    var $this = $(this);
    var num = $this.val().replace(/,/gi, "");
    var num2 = num.replace(/\d(?=(?:\d{3})+$)/g, \'$&,\');
    $this.val(num2);

})
</script>';
        return response()->view('body', compact('form', 'script'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'amount' => ['required'],
        ])->validate();

        $model = Safe::find($id);
        $model->address = $request->input('address');
        $model->description = $request->input('description');
        $model->amount = floatval(Str::replace(",","",$request->input('amount')));
        $model->currency_id = $request->input('currency_id');
        $model->active = $request->has('active');
        $model->created_at = $request->input('created_at');
        $model->save();

        return redirect()->route('home');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return int
     */
    public function destroy($id)
    {
        Safe::destroy($id);

        return 1;
    }
}
