<?php
namespace App\Repository;

use App\Entity\TokenAuth;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class TokenAuthRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(TokenAuth $entity, bool $flush = false): void
    {
        $this->entityManager->persist($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function remove(TokenAuth $entity, bool $flush = false): void
    {
        $this->entityManager->remove($entity);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function find(int $id): ?TokenAuth
    {
        return $this->entityManager->find(TokenAuth::class, $id);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(TokenAuth::class)->findAll();
    }

    public function findOneByToken(string $token): ?TokenAuth
    {
        return $this->entityManager->createQueryBuilder()
            ->select('ta')
            ->from(TokenAuth::class, 'ta')
            ->andWhere('ta.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUserId(int $userId): ?TokenAuth
    {
        return $this->entityManager->createQueryBuilder()
            ->select('ta')
            ->from(TokenAuth::class, 'ta')
            ->andWhere('ta.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUserId(int $userId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('ta')
            ->from(TokenAuth::class, 'ta')
            ->andWhere('ta.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findValidToken(string $token): ?TokenAuth
    {
        return $this->entityManager->createQueryBuilder()
            ->select('ta')
            ->from(TokenAuth::class, 'ta')
            ->andWhere('ta.token = :token')
            ->andWhere('ta.expirationDate > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteExpiredTokens(): int
    {
        return $this->entityManager->createQueryBuilder()
            ->delete(TokenAuth::class, 'ta')
            ->where('ta.expirationDate < :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }

    public function countTokensByUserId(int $userId): int
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(ta.id)')
            ->from(TokenAuth::class, 'ta')
            ->andWhere('ta.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findTokensExpiringInDays(int $days): array
    {
        $expirationDate = new DateTime();
        $expirationDate->modify("+{$days} days");

        return $this->entityManager->createQueryBuilder()
            ->select('ta')
            ->from(TokenAuth::class, 'ta')
            ->andWhere('ta.expirationDate <= :expirationDate')
            ->andWhere('ta.expirationDate > :now')
            ->setParameter('expirationDate', $expirationDate)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    public function deleteTokensByUserId(int $userId): int
    {
        return $this->entityManager->createQueryBuilder()
            ->delete(TokenAuth::class, 'ta')
            ->where('ta.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }

    public function getUserByToken(string $token): ?User
    {
        $tokenAuth = $this->findOneByToken($token);
        
        if (!$tokenAuth) {
            return null;
        }

        return $this->entityManager->find(User::class, $tokenAuth->getIdUser());
    }

    public function createTokenForUser(int $userId, string $token, DateTime $expirationDate): TokenAuth
    {
        // Verificar que el usuario existe
        $user = $this->entityManager->find(User::class, $userId);
        if (!$user) {
            throw new \InvalidArgumentException("User with ID {$userId} not found");
        }

        $tokenAuth = new TokenAuth();
        $tokenAuth->setIdUser($userId);
        $tokenAuth->setToken($token);
        $tokenAuth->setExpirationDate($expirationDate);

        $this->save($tokenAuth, true);

        return $tokenAuth;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
