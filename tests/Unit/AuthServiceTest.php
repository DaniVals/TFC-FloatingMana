<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Exception\InvalidCredentialsException;
use App\Exception\UserBlockedException;
use App\Repository\UserRepository;
use App\Service\AuthService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthServiceTest extends TestCase
{
    private $userRepository;
    private $passwordHasher;
    private $authService;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->authService = new AuthService($this->userRepository, $this->passwordHasher);
    }

    public function testLoginSuccess()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $user->expects($this->once())
            ->method('setLastLoginAt')
            ->with($this->isInstanceOf(\DateTime::class));
        $user->expects($this->once())
            ->method('setFailedLoginAttempts')
            ->with(0);
        
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($user);
        
        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(true);
        
        $this->userRepository->expects($this->exactly(2))
            ->method('save')
            ->with($user, true);
        
        // Act
        $result = $this->authService->login($email, $password);
        
        // Assert
        $this->assertSame($user, $result);
    }

    public function testLoginWithNonExistentUser()
    {
        // Arrange
        $email = 'nonexistent@example.com';
        $password = 'password123';
        
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn(null);
        
        // Assert & Act
        $this->expectException(UserBlockedException::class);
        $this->expectExceptionMessage('Usuario bloqueado o no existe');
        
        $this->authService->login($email, $password);
    }
    
    public function testLoginWithInactiveUser()
    {
        // Arrange
        $email = 'inactive@example.com';
        $password = 'password123';
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('isActive')
            ->willReturn(false);
        
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($user);
        
        // Assert & Act
        $this->expectException(UserBlockedException::class);
        $this->expectExceptionMessage('Usuario bloqueado o no existe');
        
        $this->authService->login($email, $password);
    }
    
    public function testLoginWithInvalidPassword()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'wrongpassword';
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $user->expects($this->once())
            ->method('getFailedLoginAttempts')
            ->willReturn(0);
        $user->expects($this->once())
            ->method('setFailedLoginAttempts')
            ->with(1);
        
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($user);
        
        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(false);
        
        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user, true);
        
        // Assert & Act
        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Credenciales inválidas');
        
        $this->authService->login($email, $password);
    }
    
    public function testLoginLockoutAfterTooManyAttempts()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'wrongpassword';
        
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('isActive')
            ->willReturn(true);
        $user->expects($this->once())
            ->method('getFailedLoginAttempts')
            ->willReturn(4); // Ya tiene 4 intentos, el 5º lo bloqueará
        $user->expects($this->once())
            ->method('setFailedLoginAttempts')
            ->with(5);
        $user->expects($this->once())
            ->method('setActive')
            ->with(false);
        
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($user);
        
        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(false);
        
        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user, true);
        
        // Assert & Act
        $this->expectException(InvalidCredentialsException::class);
        
        $this->authService->login($email, $password);
    }
    
    public function testRegisterSuccess()
    {
        // Arrange
        $email = 'new@example.com';
        $password = 'password123';
        $name = 'New User';
        $hashedPassword = 'hashed_password_123';
        
        // Verificar que el email no existe
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn(null);
        
        // Verificar que se crea un nuevo usuario con los datos correctos
        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);
        
        // Verificar que se guarda el usuario
        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($user) use ($email, $name, $hashedPassword) {
                return $user instanceof User
                    && $user->getEmail() === $email
                    && $user->getName() === $name
                    && $user->getPassword() === $hashedPassword
                    && $user->getRoles() === ['ROLE_USER']
                    && $user->isActive() === true;
            }), true);
        
        // Act
        $result = $this->authService->register($email, $password, $name);
        
        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($email, $result->getEmail());
        $this->assertEquals($name, $result->getName());
        $this->assertEquals($hashedPassword, $result->getPassword());
    }
    
    public function testRegisterWithExistingEmail()
    {
        // Arrange
        $email = 'existing@example.com';
        $password = 'password123';
        $name = 'Existing User';
        
        $existingUser = $this->createMock(User::class);
        
        $this->userRepository->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->willReturn($existingUser);
        
        // Assert & Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Este email ya está registrado');
        
        $this->authService->register($email, $password, $name);
    }
}
