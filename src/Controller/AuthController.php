<?php
namespace App\Controller;

use App\Service\AuthService;
use \App\Exception\UserBlockedException;
use \App\Exception\InvalidCredentialsException;
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
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $rememberMe = $request->request->get('remember_me', false);

        // Validación básica de formato
        if (empty($email) || empty($password)) {
            return $this->render('sessionManagement/login.html.twig', [
				'responseData' => [
					'success' => false,
					'message' => 'Email y contraseña son requeridos',
					'status'  => 'HTTP_BAD_REQUEST'
			        ]
                            ]
            );
        }

        try {
            // Delegar la lógica de autenticación al servicio
            $result = $this->authService->login($email, $password, $rememberMe);

            // Si el login fue exitoso, devolver token y datos de usuario
            return $this->json([
                'success' => true,
                'token' => $result['token'],
                'user' => $result['user']
            ], Response::HTTP_OK);

        } catch (InvalidCredentialsException $e) {
            return $this->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], Response::HTTP_UNAUTHORIZED);

        } catch (UserBlockedException $e) {
            return $this->json([
                'success' => false,
                'message' => 'La cuenta está bloqueada. Contacta a soporte.'
            ], Response::HTTP_FORBIDDEN);

        } catch (\Exception $e) {
            // Log el error para depuración
            $this->logger->error('Login error: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
    public function register(Request $request): JsonResponse
    {
        // Extraer datos del request
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $name = $request->request->get('name');

        // Validación básica de formato
        if (empty($email) || empty($password) || empty($name)) {
            return $this->json([
                'success' => false,
                'message' => 'Email, contraseña y nombre son requeridos'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Delegar la lógica de registro al servicio
            $user = $this->authService->register($email, $password, $name);

            return $this->json([
                'success' => true,
                'user' => $user
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            // Log el error para depuración
            $this->logger->error('Register error: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
