<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;

use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;

use App\Models\User;
use App\Models\Profile;

use App\Rules\PhoneCheck;
use App\Traits\TwilioTrait;
use App\Notifications\EmailVerification;
use Egulias\EmailValidator\EmailValidator;
use App\Http\Resources\ProfileResource;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    // use RegistersUsers;
    use TwilioTrait;
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string',  'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    public function showRegistrationForm()
    {
    }

    public function register(Request $request)
    {

        $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'email'         => !app()->isProduction() ? ['required', 'string', 'email', 'max:255', 'unique:users'] : ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'phone'         => ['required', 'unique:users', new PhoneCheck()],
            'username'      => ['required', 'string',  'max:255', 'unique:users'],
            'password'      => !app()->isProduction() ? ['required', 'confirmed', 'min:8',] : [
                'required', 'confirmed', Rules\Password::defaults(), Rules\Password::min(8)->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'account_type'  => ['required', 'string', Rule::in(['customers', 'artists', 'organizer', 'service-provider']),],
        ]);

        $formData = [
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => $request->password,
            'phone'         => $request->phone,
        ];

        // if ($request->input('reg_type') === 'phone') {

        //     $formData = [
        //         'first_name'    => ucfirst($request->first_name),
        //         'last_name'     => ucfirst($request->last_name),
        //         'username'      => $request->username,
        //         'phone'         => $request->phone,
        //         'password'      => $request->password,
        //     ];
        // }

        // $this->sendCode()
        $user = User::create($formData);
        $account = $request->input('account_type', 'customers');

        $profile = Profile::create([
            'user_id' => $user->id,
            'avatar'            => 'https://via.placeholder.com/424x424.png/006644?text=' . substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1),
            'business_email'    => $user->email,
            'phone'             => $user->phone,
            'business_name'     => $user->fullname,
            'city'              => 'Naga City',
            'zip_code'          => '4400',
            'province'          => 'Camarines Sur',
            'is_freeloader'     => $account === 'customers',
        ])->assignRole($request->input('account_type'));

        $userProfiles = Profile::with('roles', 'followers', 'following')->where('user_id', $user->id)->get();

        $userRoles = collect($userProfiles)->map(function ($query) {
            return $query->getRoleNames()->first();
        });

        $data = [
            'user_id'       => $user->id,
            'user'          => $user,
            'profile'       => new ProfileResource($profile, 's3'),
            'roles'         => $userRoles,
            'token'         => '',
        ];

        if ($account === 'artists') {

            $artistType = \App\Models\ArtistType::first();
            $genre = \App\Models\Genre::where('title', 'Others')->first();

            $client_profile = \App\Models\Artist::create([
                'profile_id'        => $profile->id,
                'artist_type_id'    => $artistType->id,
            ]);

            $languages = \App\Models\SupportedLanguage::get();
            $client_profile->genres()->sync($genre);
            $client_profile->languages()->sync($languages);
        } else if ($account === 'customers') {

            $client_profile = \App\Models\Customer::create([
                'profile_id'    => $profile->id,
                'name'          => $user->fullname,
            ]);
        } else if ($account === 'organizer') {
        } else {
        }

        if ($user->phone) {
            $user->sendCode();
        }

        event(new Registered($user));

        $user->notify(new EmailVerification($user));

        $data['token'] = $user->createToken("user_auth")->accessToken;

        return response()->json([
            'status'            => 200,
            'message'           => 'Account successfully registered.',
            'result'            => $data,
        ], 201);

        return redirect()->to('/login');
    }
}
