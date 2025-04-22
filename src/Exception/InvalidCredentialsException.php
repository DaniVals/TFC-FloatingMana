<?php
namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidCredentialsException extends AuthenticationException  {

    protected $message = 'Credenciales inválidas';

    public function __construct(string $message = 'Credenciales inválidas') {
        parent::__construct($message);
    }

    public function getMessageKey(): string {
        return $this->message;
    }

    public function getMessageData(): array {
        return [
            'success' => false,
            'message' => $this->message,
            'status'  => 401 // Código HTTP para no autorizado
        ];
    }
}
