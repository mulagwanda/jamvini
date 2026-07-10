<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Core\Hooks\Action;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Plugins\Clients\src\Models\Client;

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

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/client/dashboard';

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
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:clients,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $rules = apply_filters('client.registration.validation_rules', $rules, $data);

        return Validator::make($data, $rules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return Client
     */
    protected function create(array $data)
    {
        $client = Client::create([
            'client_number' => $this->generateClientNumber(),
            'type' => 'individual',
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'source' => 'registration',
            'password' => $data['password'],
        ]);

        Action::do('client.registration.created', $client, $data);
        Action::do('client.created', $client);

        return $client;
    }

    protected function generateClientNumber(): string
    {
        $base = 'JV-' . now()->format('ym') . '-' . str_pad((string) (Client::withTrashed()->count() + 1), 5, '0', STR_PAD_LEFT);
        $number = Str::upper($base);
        $count = 2;

        while (Client::withTrashed()->where('client_number', $number)->exists()) {
            $number = Str::upper($base . '-' . $count++);
        }

        return $number;
    }
}
