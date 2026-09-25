<?php

namespace Models;

class LoginModel
{
    function checkLogin($username, $password): bool
    {
        $correctEmail = "test@testlogin.com";
        $correctPassword = "test";

        return $correctEmail == $username && $correctPassword == $password;
    }
}