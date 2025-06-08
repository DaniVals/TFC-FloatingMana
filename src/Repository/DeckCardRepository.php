<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\DeckCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeckCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeckCard::class);
    }

    
    //Guarda una relación carta-mazo en la base de datos
    public function save(DeckCard $deckCard, bool $flush = false): void
    {
        $this->getEntityManager()->persist($deckCard);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Crear un mazo vacío
    public function createDeck(Deck $deck, bool $flush = false): void
    {
        $this->getEntityManager()->persist($deck);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    
    //Elimina una relación carta-mazo de la base de datos
    public function remove(DeckCard $deckCard, bool $flush = false): void
    {
        $this->getEntityManager()->remove($deckCard);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    
    
    //Encuentra todas las cartas de un mazo
    public function findByDeck(Deck $deck): array
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.deck = :deck')
            ->setParameter('deck', $deck)
            ->leftJoin('dc.card', 'c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    
    // Encuentra todas las cartas del mazo principal (si tienes sideboard, necesitarás agregar el campo)
    public function findMainboardCards(Deck $deck): array
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.deck = :deck')
            ->leftJoin('dc.card', 'c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //Encuentra todas las cartas del sideboard (método placeholder - necesita implementación del campo isSideboard)
    public function findSideboardCards(Deck $deck): array
    {
        // Este método necesita que agregues el campo isSideboard a tu entidad DeckCard
        // Por ahora devuelve array vacío
        return [];
    }

    // Cuenta el número de una carta específica en un mazo
    public function countCardInDeck(Deck $deck, Card $card): int
    {
        $result = $this->createQueryBuilder('dc')
            ->select('SUM(dc.cardQuantity)')
            ->andWhere('dc.deck = :deck')
            ->andWhere('dc.card = :card')
            ->setParameter('deck', $deck)
            ->setParameter('card', $card)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (int)$result : 0;
    }

    // Cuenta el número total de cartas en el mazo
    public function countCardsInDeck(Deck $deck): int
    {
        $result = $this->createQueryBuilder('dc')
            ->select('SUM(dc.cardQuantity)')
            ->andWhere('dc.deck = :deck')
            ->setParameter('deck', $deck)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (int)$result : 0;
    }

    
    // Calcula el valor total del mazo
    public function calculateDeckValue(Deck $deck): float
    {
        $result = $this->createQueryBuilder('dc')
            ->select('SUM(dc.cardQuantity * c.price)')
            ->andWhere('dc.deck = :deck')
            ->leftJoin('dc.card', 'c')
            ->setParameter('deck', $deck)
            ->getQuery()
            ->getSingleScalarResult();
            
        return $result ? (float)$result : 0.0;
    }

    // Encuentra una entrada específica de carta en un mazo
    public function findOneByDeckAndCard(Deck $deck, Card $card): ?DeckCard
    {
        return $this->createQueryBuilder('dc')
            ->andWhere('dc.deck = :deck')
            ->andWhere('dc.card = :card')
            ->setParameter('deck', $deck)
            ->setParameter('card', $card)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Obtiene la distribución de colores del mazo
    public function getColorDistribution(Deck $deck): array
    {
        $colors = ['W' => 0, 'U' => 0, 'B' => 0, 'R' => 0, 'G' => 0, 'C' => 0];
        
        $results = $this->createQueryBuilder('dc')
            ->select('c.colors, SUM(dc.cardQuantity) as count')
            ->andWhere('dc.deck = :deck')
            ->leftJoin('dc.card', 'c')
            ->setParameter('deck', $deck)
            ->groupBy('c.colors')
            ->getQuery()
            ->getResult();
            
        foreach ($results as $result) {
            $cardColors = $result['colors'] ?? [];
            if (empty($cardColors)) {
                $colors['C'] += $result['count']; // Incoloro
            } else {
                foreach ($cardColors as $color) {
                    if (isset($colors[$color])) {
                        $colors[$color] += $result['count'];
                    }
                }
            }
        }
        
        return $colors;
    }

    // Obtiene la distribución de tipos del mazo
    public function getTypeDistribution(Deck $deck): array
    {
        return $this->createQueryBuilder('dc')
            ->select('c.type, SUM(dc.cardQuantity) as count')
            ->andWhere('dc.deck = :deck')
            ->leftJoin('dc.card', 'c')
            ->setParameter('deck', $deck)
            ->groupBy('c.type')
            ->getQuery()
            ->getResult();
    }

    
    //Obtiene la distribución de coste de maná
    public function getManaCurve(Deck $deck): array
    {
        $results = $this->createQueryBuilder('dc')
            ->select('c.cmc, SUM(dc.cardQuantity) as count')
            ->andWhere('dc.deck = :deck')
            ->leftJoin('dc.card', 'c')
            ->setParameter('deck', $deck)
            ->groupBy('c.cmc')
            ->getQuery()
            ->getResult();
            
        $curve = [];
        foreach ($results as $result) {
            $cmc = min((int)$result['cmc'], 7); // Agrupar todo 7+ en una categoría
            $cmc = $cmc === 7 ? '7+' : (string)$cmc;
            
            if (!isset($curve[$cmc])) {
                $curve[$cmc] = 0;
            }
            $curve[$cmc] += (int)$result['count'];
        }
        
        ksort($curve);
        return $curve;
    }
}
