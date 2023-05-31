<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Http\Request;

use App\Http\Controllers\UsersRoleContoller;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'firstname' =>['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();
        
            $myUser = User::create([
                'name' => $input['name'],
                'firstname' => $input['firstname'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'role_id' => 77001
            ]);
            // définition de l'identifien de l'utilisateur dans la session après sa création
            MyUserController::getUserIdToSession($myUser->id);
            // défini l'utilisateur comme un usager
            $data = UsersRoleContoller::addRoleForUser($myUser->id, 77001);
            // MyUserController::getUserIdToSession($request);
            return $myUser;
        // return User::create([
        //     'name' => $input['name'],
        //     'firstname' => $input['firstname'],
        //     'email' => $input['email'],
        //     'password' => Hash::make($input['password']),
        //     'role_id' => 77001
        // ]);
    }
}
