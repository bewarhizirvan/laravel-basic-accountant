<?php

namespace App\Http\Controllers;

use App\Models\User;
use BewarHizirvan\LaravelForm\LaravelForm;
use BewarHizirvan\LaravelGrid\LaravelGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public User $user;
    public int $paginate = 50;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {

                //$this->user = User::find(Auth::user()->getAuthIdentifier());
                $this->user = Auth::user();
                if(Auth::user()->getAuthIdentifier() != 1) return redirect('/');
                return $next($request);

            }
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Customers';
        $model = User::query();
        $buttons = "<button
            class='btn btn-primary btn-sm mb-0'
            style='border-radius:20px;'
            onclick=\"location.href='".route('user.create')."'\">
            Add User
            </button>";

        $parameters = [
            'label' => $buttons,
            'checkClass' => null,
            'paginate' => 50,
            'provider' => $model,
            'footerCounter' => false
        ];
        $grid = new LaravelGrid($parameters);
        $grid->addColumn('id', 'ID');
        $grid->addColumn('name', 'Name', true);
        $grid->addColumn('email', 'Email', true);
        $grid->addColumn('created_at', 'DateTime');
        $grid->addColumn('updated_at', 'Updated');
        $grid->addActionColumn('id');
        $grid->addActionButton('account', 'Show', 'user.show');
        $grid->addActionButton('edit', 'Edit', 'user.edit');
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
            'title' => 'Add User',
            'route' => 'user.store',
            'class' => 'form-horizontal'
        ];
        $form = new LaravelForm($parameters);
        $form->addText('name', '', ['required' => 1], 'Name', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('email', '', ['type' => 'email', 'required' => 1], 'Email', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('password', '', ['type' => 'password', 'required' => 1], 'Password', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('password_confirmation', '', ['type' => 'password', 'required' => 1], 'Password Confirmation', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);

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
            'email' => ['required', 'unique:App\Models\User,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ])->validate();

        $model = new User;
        $model->name = $request->input('name');
        $model->email = $request->input('email');
        $model->password = Hash::make($request->input('password'));
        $model->save();

        return redirect()->route('user.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        $title = "Activity of - $user->name";
        $grid = SafeController::indexGrid(null, ['user_id' => $id]);
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
            'title' => 'Edit User',
            'route' => ['user.update', $id],
            'method' => 'put',
            'class' => 'form-horizontal'
        ];
        $model = User::find($id);
        $form = new LaravelForm($parameters);
        $form->addText('name', $model->name, ['required' => 1], 'Name', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('email', $model->email, ['type' => 'email', 'disabled' => 1], 'Email', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('password', '', ['type' => 'password'], 'Password', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);
        $form->addText('password_confirmation', '', ['type' => 'password'], 'Password Confirmation', ['class' => 'col-md-3 col-form-label'], ['class'=>"row"]);

        $form = $form->render();

        return response()->view('body', compact('form'));
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
            'name' => ['required'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ])->validate();

        $model = User::find($id);
        $model->name = $request->input('name');
        if($request->has('password') && $request->input('password') != '')
            $model->password = Hash::make($request->input('password'));
        $model->save();

        return redirect()->route('user.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return int
     */
    public function destroy($id)
    {
        User::destroy($id);

        return 1;
    }
}
