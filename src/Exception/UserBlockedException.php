<?php
namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserBlockedException extends AuthenticationException
{
    public function __construct(string $message = "Usuario bloqueado")
    {
        parent::__construct($message);
    }
}
