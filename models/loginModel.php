<?php

namespace models;

class loginModel
{
    function checkLogin($username, $password): bool
    {
        $correctEmail = "test@testlogin.com";
        $correctPassword = "test";

        return $correctEmail == $username && $correctPassword == $password;
    }
}