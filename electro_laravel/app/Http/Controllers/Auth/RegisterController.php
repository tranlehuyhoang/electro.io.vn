<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Models\User;
use App\Models\Wallet;
use App\Mail\VerifyMail;
use App\Rules\ReCaptcha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\ResponseCache\Facades\ResponseCache;
class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('auth.auth');
    }public function register(Request $request, User $user)
    {
        ResponseCache::clear();
        $existingUser = User::where('email', $request->input('email'))->first();
        if ($existingUser) {
            return redirect('auth/register')->with(['email' => true]);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'password' => ['required', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()]
                ,
                'g-recaptcha-response' => ['required', new ReCaptcha]
            ]);

            if ($validator->fails()) {
                return redirect('auth/register')->with(['password' => true]);
            }

            $formFields = $validator->validated();

            $formFields['otp'] = random_int(100000, 999999);
            Session::put('registeredEmail', $formFields['email']);
            Session::put('verifyStatus', null);
            $user = User::create($formFields);
            // send otp mail
            Mail::to($formFields['email'])->send(new VerifyMail($formFields['otp']));
            // create wallet with userId
            $wallet = new Wallet;
            $wallet->user_id = $user->id;
            $wallet->balance = 0;
            $wallet->save();
            //redirect
            return redirect('/verify-email')->with('message', 'welcome');
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
