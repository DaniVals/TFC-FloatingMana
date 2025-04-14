<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Excepción lanzada cuando no se encuentra una carta en el sistema.
class CardNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'La carta solicitada no fue encontrada', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }

    public static function forCardId(int $cardId): self
    {
        return new self(sprintf('No se encontró la carta con ID: %d', $cardId));
    }

    public static function forCardNameAndSet(string $cardName, string $setCode): self
    {
        return new self(sprintf("No se encontró la carta '%s' en el set '%s'", $cardName, $setCode));
    }
}
