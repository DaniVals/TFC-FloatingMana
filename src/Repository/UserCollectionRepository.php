<?php

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\User;
use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class UserCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    /**
     * Encuentra todas las cartas en la colección de un usuario
     *
     * @param User $user
     * @param array $orderBy Campos para ordenar los resultados
     * @return Collection[]
     */

    public function findByUser(User $user, array $orderBy = ['idCollection' => 'ASC']): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->setParameter('user', $user);

        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy('c.' . $field, $direction);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Encontrar por id de carta
     * @param int $idCard ID de la carta
     * @return Collection[]|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findOneByCardId(int $idCard): ?array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('card.idCard = :idCard')
            ->setParameter('idCard', $idCard)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra una carta específica en la colección de un usuario
     *
     * @param int $idCard ID de la carta
     * @param User $user
     * @return Collection[]
     */
    public function findOneByCardAndUser(Card $idCard, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.card = :card')
            ->setParameter('user', $user)
            ->setParameter('card', $idCard)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra cartas en la colección por nombre
     *
     * @param string $query Texto para buscar en el nombre de la carta
     * @param User $user
     * @return Collection[]
     */
    public function searchByNameAndUser(string $query, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('LOWER(card.name) LIKE :query')
            ->setParameter('user', $user)
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->orderBy('card.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra cartas en la colección por set (edición)
     *
     * @param string $setCode Código de la edición (ej. 'MH2')
     * @param User $user
     * @return Collection[]
     */
    public function findBySetAndUser(string $setCode, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('card.setCode = :setCode')
            ->setParameter('user', $user)
            ->setParameter('setCode', $setCode)
            ->orderBy('card.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra cartas en la colección por tipo de carta
     *
     * @param string $type Tipo de carta (ej. 'Creature', 'Instant', etc.)
     * @param User $user
     * @return Collection[]
     */
    public function findByTypeAndUser(string $type, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('card.type LIKE :type')
            ->setParameter('user', $user)
            ->setParameter('type', '%' . $type . '%')
            ->orderBy('card.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra cartas en la colección por color
     *
     * @param array $colors Array de colores (ej. ['W', 'U'])
     * @param bool $exactMatch Si debe coincidir exactamente con los colores
     * @param User $user
     * @return Collection[]
     */
    public function findByColorsAndUser(array $colors, bool $exactMatch, User $user): array
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->setParameter('user', $user);

        if ($exactMatch) {
            // Para coincidencia exacta, necesitamos verificar que la carta tenga exactamente estos colores
            $colorString = implode('', $colors);
            $qb->andWhere('card.colors = :colors')
               ->setParameter('colors', $colorString);
        } else {
            // Para coincidencia parcial, verificamos que la carta tenga al menos uno de estos colores
            foreach ($colors as $index => $color) {
                $paramName = 'color' . $index;
                $qb->andWhere('card.colors LIKE :' . $paramName)
                   ->setParameter($paramName, '%' . $color . '%');
            }
        }

        return $qb->orderBy('card.name', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Encuentra cartas con un valor mayor o igual al especificado
     * 
     * @param float $minValue Valor mínimo
     * @param User $user
     * @return Collection[]
     */
    public function findByMinValueAndUser(float $minValue, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('card.price >= :minValue')
            ->setParameter('user', $user)
            ->setParameter('minValue', $minValue)
            ->orderBy('card.price', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra cartas con un valor menor o igual al especificado
     * 
     * @param float $maxValue Valor máximo
     * @param User $user
     * @return Collection[]
     */
    public function findByMaxValueAndUser(float $maxValue, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('card.price <= :maxValue')
            ->setParameter('user', $user)
            ->setParameter('maxValue', $maxValue)
            ->orderBy('card.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra las cartas más valiosas en la colección de un usuario
     * 
     * @param User $user
     * @param int $limit Cantidad de cartas a devolver
     * @return Collection[]
     */
    public function findMostValuableCards(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('card.price IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('card.price', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Cuenta el número total de cartas en la colección de un usuario
     * (teniendo en cuenta la cantidad de cada carta)
     * 
     * @param User $user
     * @return int
     */
    public function countTotalCards(User $user): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.quantity) as total')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
            
        return (int) ($result ?? 0);
    }

    /**
     * Calcula el valor total de la colección de un usuario
     * 
     * @param User $user
     * @return float
     */
    public function calculateCollectionValue(User $user): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.quantity * card.price) as totalValue')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('card.price IS NOT NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
            
        return (float) ($result ?? 0);
    }

    /**
     * Obtiene estadísticas de rareza en la colección
     * 
     * @param User $user
     * @return array Arreglo asociativo con cantidades por rareza
     */
    public function getRarityStats(User $user): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('card.rarity as rarity, SUM(c.quantity) as count')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->groupBy('card.rarity')
            ->getQuery()
            ->getResult();
            
        // Convertir el resultado a un arreglo asociativo
        $stats = [];
        foreach ($result as $row) {
            $stats[$row['rarity']] = (int) $row['count'];
        }
        
        return $stats;
    }

    /**
     * Guarda una entidad Collection en la base de datos
     * 
     * @param Collection $collection
     * @param bool $flush Si se debe ejecutar el flush inmediatamente
     */
    public function save(Collection $collection, bool $flush = true): void
    {
        $this->_em->persist($collection);
        
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Elimina una entidad Collection de la base de datos
     * 
     * @param Collection $collection
     * @param bool $flush Si se debe ejecutar el flush inmediatamente
     */
    public function remove(Collection $collection, bool $flush = true): void
    {
        $this->_em->remove($collection);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findMostValuableCardsByUser(User $user, int $limit): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'card')
            ->join('c.card', 'card')
            ->where('c.user = :user')
            ->andWhere('c.purchasePrice IS NOT NULL')
            ->setParameter('user', $user)
            ->orderBy('c.purchasePrice', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
