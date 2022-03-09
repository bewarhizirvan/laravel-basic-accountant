<?php

namespace App\Http\Controllers;

use App\Models\Safe;
use BewarHizirvan\LaravelGrid\LaravelGrid;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Home';
        $buttons = "<button
            class='btn btn-primary btn-sm mb-0'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'spend')."'\">
            Add Spend
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'cash_in')."'\">
            Add Cash-In
            </button>";
        $buttons .= "<button
            class='btn btn-primary btn-sm mb-0 ms-1'
            style='border-radius:20px;'
            onclick=\"location.href='".route('safe.create', 'cash_out')."'\">
            Add Cash-Out
            </button>";
        $grid = SafeController::indexGrid($buttons);
        return response()->view('body', compact('grid', 'title'));

        $model = Safe::query();
        $parameters = [
            'label' => $buttons,
            'checkClass' => null,
            'paginate' => 50,
            'provider' => $model,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->addColumn('full_name', 'Full Name');
        $grid->addColumn('description', 'Description');
        $grid->addColumn('address', 'Address');
        $grid->addColumn('type', 'Type');
        $grid->addColumn('direction', 'Direction');
        $grid->addColumn('amount', 'Amount', false, false, function ($row) {
            return $row->amount .' '. $row->currency->code;
        });
        $grid->addColumn('wallet.name', 'Wallet');
        $grid->addColumn('active', 'Active', false, false, null, function ($val) {
            return ($val) ? LaravelGrid::OK : LaravelGrid::NOTOK;
        });
        $grid->addActionColumn('id');
        $grid->addActionButton('edit', 'Edit', 'home');

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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
