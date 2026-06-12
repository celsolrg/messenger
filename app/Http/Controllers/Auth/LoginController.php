<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class LoginController
{
    protected function credentials(Request $request)
    {
        $login = $request->input('login');

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        return [
            $field => $login,
            'password' => $request->password,
            'status' => 'active',
            'allow_login' => 1,
        ];
    }
}