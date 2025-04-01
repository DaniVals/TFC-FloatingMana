<?php
namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidCredentialsException extends AuthenticationException
{
    public function __construct(string $message = "Credenciales inválidas")
    {
        parent::__construct($message);
    }
}

