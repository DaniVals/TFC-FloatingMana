<?php
namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    
    // Guarda una carta en la base de datos
    public function save(Card $card, bool $flush = false): void
    {
        $this->getEntityManager()->persist($card);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findId(string $cardId): ?Card
    {
       return $this->createQueryBuilder('c')
            ->andWhere('c.idCard = :id')
            ->setParameter('id', $cardId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findIdScryfall(string $cardId): ?Card
    {
       return $this->createQueryBuilder('c')
            ->andWhere('c.idScryfall = :idCard')
            ->setParameter('idCard', $cardId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // Busca cartas por nombre
    public function findByNamePartial(string $name, int $limit = 20): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('c.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    
    //Busca cartas por formato de legalidad
    public function findByFormat(string $format): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.legalities LIKE :format')
            ->setParameter('format', '%"' . $format . '":"legal"%')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Busca cartas por tipo
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.type LIKE :type')
            ->setParameter('type', '%' . $type . '%')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    //Busca cartas por color
    public function findByColor(array $colors): array
    {
        $qb = $this->createQueryBuilder('c');
        
        foreach ($colors as $index => $color) {
            $qb->andWhere('c.colors LIKE :color' . $index)
               ->setParameter('color' . $index, '%"' . $color . '"%');
        }
        
        return $qb->orderBy('c.name', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    //Busca cartas por conjunto (set)
    public function findBySet(string $set): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.set = :set')
            ->setParameter('set', $set)
            ->orderBy('c.number', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //Busca cartas con búsqueda avanzada
    public function findByAdvancedSearch(array $criteria): array
    {
        //TODO
        return [];
    }
    
    //Actualiza los precios de las cartas
    public function updatePrices(array $data): int
    {
        $count = 0;
        
        foreach ($data as $cardData) {
            $card = $this->findOneBy(['scryfallId' => $cardData['id']]);
            
            if ($card) {
                $card->setPrice($cardData['price'] ?? 0);
                $this->save($card);
                $count++;
            }
        }
        
        if ($count > 0) {
            $this->getEntityManager()->flush();
        }
        
        return $count;
    }

    


}
