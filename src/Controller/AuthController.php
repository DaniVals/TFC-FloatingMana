<?php
namespace App\Controller;

use App\Service\AuthService;
use App\Exception\UserBlockedException;
use App\Exception\InvalidCredentialsException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController {

    private $logger;
    private $authService;

    public function __construct(AuthService $authService, LoggerInterface $logger) {
        $this->logger = $logger;
        $this->authService = $authService;
    }

    #[Route('/app/login', name: 'app_login_view', methods: ['GET'])]
    public function loginView(): Response
    {
        return $this->render('sessionManagement/login.html.twig');
    }

    #[Route('/app/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request)
    {
        // Extraer credenciales del request
        $email = $request->request->get('_email');
        $password = $request->request->get('_password');

        // Validación básica de formato
        if (empty($email) || empty($password)) {
            return $this->render('sessionManagement/login.html.twig', [
				'responseData' => [
					'success' => false,
					'message' => 'Email y contraseña son requeridos',
					'status'  => Response::HTTP_BAD_REQUEST
			        ]
                            ]
            );
        }

        try {
            // Delegar la lógica de autenticación al servicio
            $result = $this->authService->login($email, $password);

            // Si el login fue exitoso, devolver token y datos de usuario
            return $this->render('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => true,
                    'token' => $result['token'],
                    'user' => $result['user'],
                    'status'  => Response::HTTP_OK
                ]
            ]);

        } catch (InvalidCredentialsException $e) {
            return $this->render('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'status'  => $e->getMessageData()['status']
                ]
            ]);
        } catch (UserBlockedException $e) {
            return $this->render('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Usuario bloqueado',
                    'status'  => Response::HTTP_FORBIDDEN
                ]
            ]);

        } catch (\Exception $e) {
            // Log el error para depuración
            $this->logger->error('Login error: ' . $e->getMessage());
            
            return $this->render('sessionManagement/login.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Error interno del servidor',
                    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
                ]
            ]);
        }
    }

    #[Route('/app/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Aquí puedes manejar la lógica de logout si es necesario
        return $this->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ], Response::HTTP_OK);
    }

    #[Route('/app/register', name: 'app_register_view', methods: ['GET'])]
    public function registerView(): Response
    {
        return $this->render('sessionManagement/register.html.twig');
    }

    #[Route('/app/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request)
    {
        // Extraer datos del request
        $name = $request->request->get('_username'); $email = $request->request->get('_email'); $password = $request->request->get('_password'); // Validación básica de formato
        if (empty($email) || empty($password) || empty($name)) {
            return $this->render('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Email, contraseña y nombre son requeridos',
                    'status'  => Response::HTTP_BAD_REQUEST
                ]
            ]);
        }

        try {
            // Delegar la lógica de registro al servicio
            $user = $this->authService->register($email, $password, $name);

            return $this->render('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => true,
                    'user' => $user,
                    'status'  => Response::HTTP_OK
                ]
            ]);

        } catch (\Exception $e) {
            // Log el error para depuración
            $this->logger->error('Register error: ' . $e->getMessage());

            return $this->render('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => 'Error interno del servidor',
                    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
                ]
            ]);
        }
    }
}
