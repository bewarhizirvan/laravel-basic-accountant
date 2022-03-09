<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    public User $user;
    public int $paginate = 50;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {

                //$this->user = User::find(Auth::user()->getAuthIdentifier());
                $this->user = Auth::user();
                return $next($request);

            }
            return $next($request);
        });
    }
}
