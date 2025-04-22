<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Controller\AuthController;
use App\Service\AuthService;
use App\Exception\UserBlockedException;
use App\Exception\InvalidCredentialsException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthControllerTest extends TestCase
{
    private $authService;
    private $logger;
    private $controller;
    private $entityManager;

    protected function setUp(): void
    {
        // Crear mocks para las dependencias
        $this->authService = $this->createMock(AuthService::class);
        $this->entityManager = $this->createMock(UserRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // Crear instancia del controlador con dependencias mockeadas
        $this->controller = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authService, $this->logger])
            ->onlyMethods(['render', 'json'])
            ->getMock();
    }


    public function testLoginSuccess()
    {
        // Crear un usuario en la base de datos (simulado o real)
        $user = new User();
        $user->setId(5);
        $user->setName('Test User');
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->setActive(true);
        // Flush al user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $result = $this->authService->login($user->getEmail(), $user->getPassword());

        // Asserts para verificar el resultado
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->getId(), $result->getId());
        $this->assertEquals($user->getName(), $result->getName());
        $this->assertEquals($user->getEmail(), $result->getEmail());
        $this->assertEquals($user->getPassword(), $result->getPassword());
        $this->assertTrue($result->isActive());
        // Configurar el mock del método render
        $response = new Response('Login success response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/main.html.twig',
                $this->callback(function($parameters) use ($user) {
                    return $parameters['responseData']['success'] === true &&
                        $parameters['responseData']['user'] === $user &&
                        $parameters['responseData']['status'] === Response::HTTP_OK;
                })
            )
            ->willReturn($response);
        // Ejecutar el método
        $request = new Request([], [
            '_email' => $user->getEmail(),
            '_password' => $user->getPassword()
        ]);
        $result = $this->controller->login($request);
        $this->assertSame($response, $result);

        // Eliminar el usuario de la base de datos
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function testLoginInvalidCredentials()
    {
        // Crear una solicitud con credenciales
        $request = new Request([], [
            '_email' => 'usuario@example.com',
            '_password' => 'password123'
        ]);

        // Configurar el mock para lanzar una excepción de credenciales inválidas
        $this->authService->expects($this->once())
            ->method('login')
            ->willThrowException(new InvalidCredentialsException('Credenciales inválidas', [
                'status' => Response::HTTP_UNAUTHORIZED
            ]));

        // Configurar el mock del método render
        $response = new Response('Invalid credentials response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/login.html.twig',
                $this->callback(function($parameters) {
                    return $parameters['responseData']['success'] === false &&
                        $parameters['responseData']['message'] === 'Credenciales inválidas' &&
                        $parameters['responseData']['status'] === Response::HTTP_UNAUTHORIZED;
                })
            )
            ->willReturn($response);

        // Ejecutar el método
        $result = $this->controller->login($request);
        $this->assertSame($response, $result);
    }

    public function testLoginUserBlocked()
    {
        // Crear una solicitud con credenciales
        $request = new Request([], [
            '_email' => 'usuario@example.com',
            '_password' => 'password123'
        ]);

        // Configurar el mock para lanzar una excepción de usuario bloqueado
        $this->authService->expects($this->once())
            ->method('login')
            ->willThrowException(new UserBlockedException('Usuario bloqueado'));

        // Configurar el mock del método render
        $response = new Response('User blocked response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/login.html.twig',
                $this->callback(function($parameters) {
                    return $parameters['responseData']['success'] === false &&
                        $parameters['responseData']['message'] === 'Usuario bloqueado' &&
                        $parameters['responseData']['status'] === Response::HTTP_FORBIDDEN;
                })
            )
            ->willReturn($response);

        // Ejecutar el método
        $result = $this->controller->login($request);
        $this->assertSame($response, $result);
    }

    public function testLoginServerError()
    {
        // Crear una solicitud con credenciales
        $request = new Request([], [
            '_email' => 'usuario@example.com',
            '_password' => 'password123'
        ]);

        // Configurar el mock para lanzar una excepción genérica
        $this->authService->expects($this->once())
            ->method('login')
            ->willThrowException(new \Exception('Error en el servidor'));

        // Configurar que el logger registre el error
        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Login error'));

        // Configurar el mock del método render
        $response = new Response('Server error response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/login.html.twig',
                $this->callback(function($parameters) {
                    return $parameters['responseData']['success'] === false &&
                        $parameters['responseData']['message'] === 'Error interno del servidor' &&
                        $parameters['responseData']['status'] === Response::HTTP_INTERNAL_SERVER_ERROR;
                })
            )
            ->willReturn($response);

        // Ejecutar el método
        $result = $this->controller->login($request);
        $this->assertSame($response, $result);
    }

    public function testLogout()
    {
        // Configurar el mock del método json
        $jsonResponse = new JsonResponse([
            'success' => true,
            'message' => 'Logout exitoso'
        ], Response::HTTP_OK);

        $this->controller->expects($this->once())
            ->method('json')
            ->with(
                [
                    'success' => true,
                    'message' => 'Logout exitoso'
                ],
                Response::HTTP_OK
            )
            ->willReturn($jsonResponse);

        // Ejecutar el método
        $result = $this->controller->logout();
        $this->assertSame($jsonResponse, $result);
    }

    public function testRegisterSuccess()
    {
        // Crear una solicitud con los datos de registro
        $request = new Request([], [
            '_username' => 'Nuevo Usuario',
            '_email' => 'nuevo@example.com',
            '_password' => 'password123'
        ]);

        // Crear un mock de UserInterface para el usuario registrado
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('nuevo@example.com');

        $this->authService->expects($this->once())
            ->method('register')
            ->with('nuevo@example.com', 'password123', 'Nuevo Usuario')
            ->willReturn($user);

        // Configurar el mock del método render
        $response = new Response('Register success response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/register.html.twig',
                $this->callback(function($parameters) use ($user) {
                    return $parameters['responseData']['success'] === true &&
                        $parameters['responseData']['user'] === $user &&
                        $parameters['responseData']['status'] === Response::HTTP_OK;
                })
            )
            ->willReturn($response);

        // Ejecutar el método
        $result = $this->controller->register($request);
        $this->assertSame($response, $result);
    }

    public function testRegisterEmptyFields()
    {
        // Crear una solicitud sin datos completos
        $request = new Request([], [
            '_username' => '',
            '_email' => 'nuevo@example.com',
            '_password' => 'password123'
        ]);

        // Configurar el mock del método render
        $response = new Response('Empty fields response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/register.html.twig',
                $this->callback(function($parameters) {
                    return $parameters['responseData']['success'] === false &&
                        $parameters['responseData']['message'] === 'Email, contraseña y nombre son requeridos' &&
                        $parameters['responseData']['status'] === Response::HTTP_BAD_REQUEST;
                })
            )
            ->willReturn($response);

        // Ejecutar el método
        $result = $this->controller->register($request);
        $this->assertSame($response, $result);
    }

    public function testRegisterServerError()
    {
        // Crear una solicitud con datos de registro
        $request = new Request([], [
            '_username' => 'Nuevo Usuario',
            '_email' => 'nuevo@example.com',
            '_password' => 'password123'
        ]);

        // Configurar el mock para lanzar una excepción
        $this->authService->expects($this->once())
            ->method('register')
            ->willThrowException(new \Exception('Error en el registro'));

        // Configurar que el logger registre el error
        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Register error'));

        // Configurar el mock del método render
        $response = new Response('Register server error response');
        $this->controller->expects($this->once())
            ->method('render')
            ->with(
                'sessionManagement/register.html.twig',
                $this->callback(function($parameters) {
                    return $parameters['responseData']['success'] === false &&
                        $parameters['responseData']['message'] === 'Error interno del servidor' &&
                        $parameters['responseData']['status'] === Response::HTTP_INTERNAL_SERVER_ERROR;
                })
            )
            ->willReturn($response);

        // Ejecutar el método
        $result = $this->controller->register($request);
        $this->assertSame($response, $result);
    }
}
