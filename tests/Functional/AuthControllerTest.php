<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Exception\InvalidCredentialsException;
use App\Exception\UserBlockedException;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    public function testLoginViewRendersCorrectly()
    {
        $client = static::createClient();
        $client->request('GET', '/app/login');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[action="/app/login"]');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }
    
    public function testLoginWithEmptyCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/app/login');
        
        $form = $crawler->selectButton('Iniciar sesión')->form();
        $client->submit($form, [
            '_username' => '',
            '_password' => ''
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Email y contraseña son requeridos');
    }
    
    public function testLoginWithValidCredentials()
    {
        $client = static::createClient();
        
        // Mock del AuthService
        $authServiceMock = $this->createMock(AuthService::class);
        $authServiceMock->expects($this->once())
            ->method('login')
            ->with('user@example.com', 'password123', false)
            ->willReturn([
                'token' => 'fake_token_123',
                'user' => [
                    'id' => 1,
                    'email' => 'user@example.com',
                    'name' => 'Test User'
                ]
            ]);
        
        // Reemplazar el servicio real con el mock
        $client->getContainer()->set('App\Service\AuthService', $authServiceMock);
        
        $crawler = $client->request('GET', '/app/login');
        $form = $crawler->selectButton('Iniciar sesión')->form();
        $client->submit($form, [
            '_username' => 'user@example.com',
            '_password' => 'password123'
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'Login exitoso');
        $this->assertSelectorTextContains('.user-info', 'Test User');
    }
    
    public function testLoginWithInvalidCredentials()
    {
        $client = static::createClient();
        
        // Mock del AuthService que lanza una excepción
        $authServiceMock = $this->createMock(AuthService::class);
        $authServiceMock->expects($this->once())
            ->method('login')
            ->willThrowException(new InvalidCredentialsException('Credenciales inválidas'));
        
        // Reemplazar el servicio real con el mock
        $client->getContainer()->set('App\Service\AuthService', $authServiceMock);
        
        $crawler = $client->request('GET', '/app/login');
        $form = $crawler->selectButton('Iniciar sesión')->form();
        $client->submit($form, [
            '_username' => 'user@example.com',
            '_password' => 'wrong_password'
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Credenciales inválidas');
    }
    
    public function testLoginWithBlockedUser()
    {
        $client = static::createClient();
        
        // Mock del AuthService que lanza una excepción
        $authServiceMock = $this->createMock(AuthService::class);
        $authServiceMock->expects($this->once())
            ->method('login')
            ->willThrowException(new UserBlockedException('Usuario bloqueado'));
        
        // Reemplazar el servicio real con el mock
        $client->getContainer()->set('App\Service\AuthService', $authServiceMock);
        
        $crawler = $client->request('GET', '/app/login');
        $form = $crawler->selectButton('Iniciar sesión')->form();
        $client->submit($form, [
            '_username' => 'blocked@example.com',
            '_password' => 'password123'
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Usuario bloqueado');
    }
    
    public function testLogoutReturnsSuccessResponse()
    {
        $client = static::createClient();
        $client->request('POST', '/app/logout');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);
        
        $this->assertTrue($data['success']);
        $this->assertEquals('Logout exitoso', $data['message']);
    }
    
    public function testRegisterViewRendersCorrectly()
    {
        $client = static::createClient();
        $client->request('GET', '/app/register');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[action="/app/register"]');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_email"]');
        $this->assertSelectorExists('input[name="_password"]');
    }
    
    public function testRegisterWithEmptyFields()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/app/register');
        
        $form = $crawler->selectButton('Registrarse')->form();
        $client->submit($form, [
            '_username' => '',
            '_email' => '',
            '_password' => ''
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Email, contraseña y nombre son requeridos');
    }
    
    public function testRegisterWithValidData()
    {
        $client = static::createClient();
        
        // Crear un objeto User para el resultado simulado
        $user = new User();
        $user->setId(1);
        $user->setEmail('new@example.com');
        $user->setName('New User');
        
        // Mock del AuthService
        $authServiceMock = $this->createMock(AuthService::class);
        $authServiceMock->expects($this->once())
            ->method('register')
            ->with('new@example.com', 'password123', 'New User')
            ->willReturn($user);
        
        // Reemplazar el servicio real con el mock
        $client->getContainer()->set('App\Service\AuthService', $authServiceMock);
        
        $crawler = $client->request('GET', '/app/register');
        $form = $crawler->selectButton('Registrarse')->form();
        $client->submit($form, [
            '_username' => 'New User',
            '_email' => 'new@example.com',
            '_password' => 'password123'
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'Registro exitoso');
    }
    
    public function testRegisterWithExistingEmail()
    {
        $client = static::createClient();
        
        // Mock del AuthService que lanza una excepción
        $authServiceMock = $this->createMock(AuthService::class);
        $authServiceMock->expects($this->once())
            ->method('register')
            ->willThrowException(new \Exception('Este email ya está registrado'));
        
        // Reemplazar el servicio real con el mock
        $client->getContainer()->set('App\Service\AuthService', $authServiceMock);
        
        $crawler = $client->request('GET', '/app/register');
        $form = $crawler->selectButton('Registrarse')->form();
        $client->submit($form, [
            '_username' => 'Existing User',
            '_email' => 'existing@example.com',
            '_password' => 'password123'
        ]);
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Error interno del servidor');
    }
}
