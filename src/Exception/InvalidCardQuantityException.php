<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

// Excepción lanzada cuando se intenta añadir una cantidad inválida de cartas.
class InvalidCardQuantityException extends BadRequestHttpException
{
    private int $quantity;
    private string $cardName;

    public function __construct(string $message = 'La cantidad de cartas especificada no es válida', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }

    public static function forCardQuantity(int $quantity, string $cardName): self
    {
        $exception = new self(sprintf("Cantidad inválida (%d) para la carta '%s'", $quantity, $cardName));
        $exception->quantity = $quantity;
        $exception->cardName = $cardName;
        
        return $exception;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCardName(): string
    {
        return $this->cardName;
    }
}
