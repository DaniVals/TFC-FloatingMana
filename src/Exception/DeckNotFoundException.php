<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Excepción lanzada cuando no se encuentra un mazo en el sistema.
class DeckNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'El mazo solicitado no fue encontrado', ?\Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }

    public static function forDeckId(int $deckId): self
    {
        return new self(sprintf('No se encontró el mazo con ID: %d', $deckId));
    }
}
