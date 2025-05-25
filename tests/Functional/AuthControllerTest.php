<?php

namespace App\Tests\Controller;

use App\Controller\AuthController;
use App\Entity\User;
use App\Service\AuthService;
use App\Exception\UserBlockedException;
use App\Exception\InvalidCredentialsException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AuthControllerTest extends KernelTestCase
{
    private $authController;
    private $authServiceMock;
    private $loggerMock;

    protected function setUp(): void
    {
        $this->authServiceMock = $this->createMock(AuthService::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        /** @var AuthService $authServiceMock **/
        $authServiceMock = $this->authServiceMock;
        /** @var LoggerInterface $loggerMock **/
        $loggerMock = $this->loggerMock;

        $this->authController = new AuthController(
            $authServiceMock, 
            $loggerMock);
    }

    public function testLoginView()
    {
        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/login.html.twig')
            ->willReturn(new Response());

        $response = $this->authController->loginView();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testLoginWithEmptyCredentials()
    {
        // Crear una request simulada con parámetros vacíos
        $request = new Request();
        $request->request->set('_username', '');
        $request->request->set('_password', '');

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Email y contraseña son requeridos',
                    'status'  => Response::HTTP_BAD_REQUEST
                ]
            ])
            ->willReturn(new Response());

        $response = $this->authController->login($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testLoginSuccess()
    {
        // Crear un usuario simulado
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getName')->willReturn('Test User');
        $user->method('getEmail')->willReturn('test@example.com');

        // Crear una request simulada con credenciales válidas
        $request = new Request();
        $request->request->set('_username', 'test@example.com');
        $request->request->set('_password', 'password123');

        // Configurar el servicio de autenticación para devolver el usuario simulado
        $this->authServiceMock->expects($this->once())
            ->method('login')
            ->with('test@example.com', 'password123')
            ->willReturn($user);

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('dashboard/index.html.twig', $this->callback(function($arg) {
                // Verificar que la respuesta contenga los datos esperados
                return isset($arg['responseData']) && 
                       $arg['responseData']['success'] === true && 
                       isset($arg['responseData']['token']) && 
                       $arg['responseData']['message'] === 'Login exitoso' &&
                       isset($arg['responseData']['user']);
            }))
            ->willReturn(new Response());

        $response = $this->authController->login($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testLoginWithInvalidCredentials()
    {
        // Crear una request simulada
        $request = new Request();
        $request->request->set('_username', 'test@example.com');
        $request->request->set('_password', 'wrongpassword');

        // Configurar el servicio de autenticación para lanzar una excepción
        $this->authServiceMock->expects($this->once())
            ->method('login')
            ->with('test@example.com', 'wrongpassword')
            ->willThrowException(new InvalidCredentialsException('Credenciales inválidas', [
                'status' => Response::HTTP_UNAUTHORIZED
            ]));

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Credenciales inválidas',
                    'status'  => Response::HTTP_UNAUTHORIZED
                ]
            ])
            ->willReturn(new Response());

        $response = $this->authController->login($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testLoginWithBlockedUser()
    {
        // Crear una request simulada
        $request = new Request();
        $request->request->set('_username', 'blocked@example.com');
        $request->request->set('_password', 'password123');

        // Configurar el servicio de autenticación para lanzar una excepción
        $this->authServiceMock->expects($this->once())
            ->method('login')
            ->with('blocked@example.com', 'password123')
            ->willThrowException(new UserBlockedException());

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Usuario bloqueado',
                    'status'  => Response::HTTP_FORBIDDEN
                ]
            ])
            ->willReturn(new Response());

        $response = $this->authController->login($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testLoginWithServerError()
    {
        // Crear una request simulada
        $request = new Request();
        $request->request->set('_username', 'test@example.com');
        $request->request->set('_password', 'password123');

        // Configurar el servicio de autenticación para lanzar una excepción genérica
        $this->authServiceMock->expects($this->once())
            ->method('login')
            ->with('test@example.com', 'password123')
            ->willThrowException(new \Exception('Error de servidor'));

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Error interno del servidor',
                    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
                ]
            ])
            ->willReturn(new Response());

        // Esperamos que se registre el error en el logger
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Login error: Error de servidor');

        $response = $this->authController->login($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testLogout()
    {
        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $jsonResponse = new JsonResponse([
            'success' => true,
            'message' => 'Logout exitoso',
            'status'  => Response::HTTP_OK
        ]);

        $this->authController->expects($this->once())
            ->method('render')
            ->with('app/index.html.twig', [
                'responseData' => [
                    'success' => true,
                    'message' => 'Logout exitoso',
                    'status'  => Response::HTTP_OK
                ]
            ])
            ->willReturn($jsonResponse);

        $response = $this->authController->logout();
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testRegisterView()
    {
        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/register.html.twig')
            ->willReturn(new Response());

        $response = $this->authController->registerView();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRegisterWithEmptyFields()
    {
        // Crear una request simulada con parámetros vacíos
        $request = new Request();
        $request->request->set('_username', '');
        $request->request->set('_email', '');
        $request->request->set('_password', '');

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Email, contraseña y nombre son requeridos',
                    'status'  => Response::HTTP_BAD_REQUEST
                ]
            ])
            ->willReturn(new Response());

        $response = $this->authController->register($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRegisterSuccess()
    {
        // Crear un usuario simulado
        $user = new User();
        $user->setId(1);
        $user->setName('Nuevo Usuario');
        $user->setEmail('nuevo@example.com');
        $user->setPassword('password123');

        // Crear una request simulada con datos válidos
        $request = new Request();
        $request->request->set('_username', 'Nuevo Usuario');
        $request->request->set('_email', 'nuevo@example.com');
        $request->request->set('_password', 'password123');

        // Configurar el servicio de autenticación para devolver el usuario simulado
        $this->authServiceMock->expects($this->once())
            ->method('register')
            ->with('nuevo@example.com', 'password123', 'Nuevo Usuario')
            ->willReturn($user);

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => true,
                    'user' => $user,
                    'message' => 'Registro exitoso',
                    'status'  => Response::HTTP_OK
                ]
            ])
            ->willReturn(new Response());

        $response = $this->authController->register($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRegisterWithServerError()
    {
        // Crear una request simulada
        $request = new Request();
        $request->request->set('_username', 'Nuevo Usuario');
        $request->request->set('_email', 'nuevo@example.com');
        $request->request->set('_password', 'password123');

        // Configurar el servicio de autenticación para lanzar una excepción genérica
        $this->authServiceMock->expects($this->once())
            ->method('register')
            ->with('nuevo@example.com', 'password123', 'Nuevo Usuario')
            ->willThrowException(new \Exception('Error de registro'));

        // Configurar el método render para devolver una respuesta simulada
        $this->authController = $this->getMockBuilder(AuthController::class)
            ->setConstructorArgs([$this->authServiceMock, $this->loggerMock])
            ->onlyMethods(['render'])
            ->getMock();

        $this->authController->expects($this->once())
            ->method('render')
            ->with('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Error interno del servidor',
                    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
                ]
            ])
            ->willReturn(new Response());

        // Esperamos que se registre el error en el logger
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('Register error: Error de registro');

        $response = $this->authController->register($request);
        $this->assertInstanceOf(Response::class, $response);
    }
}
