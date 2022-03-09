<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class AjaxController extends BaseController
{
    public function customer(Request $request)
    {
        $result = [];

        if( $request->has('full_name') && $request->input('full_name') != '')
        {
            $full_name = $request->input('full_name');
            $model = Customer::select('id', 'full_name')->where('active',1)->where('full_name','like',"%$full_name%");
            foreach($model->get() as $row)
            {
                $result[] = ['value' => $row->full_name, 'customer_id' => $row->id];
            }

        }
        return response()->json($result);
    }
}
