<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Exception\InvalidCredentialsException;
use App\Exception\UserBlockedException;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthService {
    private $userRepository;
    private $passwordHasher;
    
    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,

    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }
    
    //Maneja la lógica de negocio del login
    public function login(string $email, string $password): User {
        // Buscar usuario por email
        $user = $this->userRepository->findOneByEmail($email);
        
        // Verificar si el usuario existe y está activo
        if (!$user || !$user->isActive()) {
            throw new UserBlockedException('Usuario bloqueado o no existe');
        }
        
        // Verificar la contraseña
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $this->incrementFailedLoginAttempts($user);
            throw new InvalidCredentialsException('Credenciales inválidas');
        }
        
        // Resetear intentos fallidos
        $this->resetFailedLoginAttempts($user);
        
        // Actualizar fecha de último login
        // $user->setLastLoginAt(new \DateTime());
        
        // Guardar cambios en la base de datos
        $this->userRepository->save($user, true);
        
        return $user;
    }
    
    //Método para registrar un nuevo usuario
    public function register(string $email, string $password, string $name): UserInterface {
        // Verificar si el email ya está registrado
        if ($this->userRepository->findOneByEmail($email)) {
            throw new \Exception('Este email ya está registrado');
        }
        
        // Crear nuevo usuario
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles(['ROLE_USER']);
        $user->setActive(1);
        // $user->setCreatedAt(new \DateTime());
        
        // Hashear contraseña usando el hasher de Symfony
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Guardar usuario
        $this->userRepository->save($user, true);
        
        return $user;
    }
    
    
    //Incrementa el contador de intentos fallidos
    private function incrementFailedLoginAttempts($user): void
    {
        $attempts = $user->getFailedLoginAttempts() + 1;
        $user->setFailedLoginAttempts($attempts);
        
        // Bloquear cuenta después de demasiados intentos
        if ($attempts >= 5) {
            $user->setActive(false);
        }
        
        $this->userRepository->save($user, true);
    }
    
    //Resetea el contador de intentos fallidos
    private function resetFailedLoginAttempts($user): void
    {
        $user->setFailedLoginAttempts(0);
        $this->userRepository->save($user, true);
    }
}
