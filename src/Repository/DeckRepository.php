<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deck::class);
    }

    //Guarda un mazo en la base de datos
    public function save(Deck $deck, bool $flush = false): void
    {
        $this->getEntityManager()->persist($deck);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Elimina un mazo de la base de datos
    public function remove(Deck $deck, bool $flush = false): void
    {
        $this->getEntityManager()->remove($deck);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Busca todos los mazos de un usuario
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.user = :user')
            ->setParameter('user', $user)
            ->orderBy('d.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Busca mazos por formato
    public function findByFormat(string $format): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.format = :format')
            ->setParameter('format', $format)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Encuentra un mazo específico de un usuario
    public function findOneByIdAndUser(int $id, User $user): ?Deck
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.idDeck = :idDeck')
            ->andWhere('d.idUser = :idUser')
            ->setParameter('idDeck', $id)
            ->setParameter('idUser', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneById(int $id): ?Deck
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.idDeck = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Busca mazos por nombre (para búsquedas)
    public function findByNameContaining(string $name): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('LOWER(d.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Encuentra los mazos más recientes
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
