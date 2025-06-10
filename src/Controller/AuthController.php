<?php
namespace App\Controller;

use App\Service\AuthService;
use App\Exception\UserBlockedException;
use App\Exception\InvalidCredentialsException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController {

    private $logger;
    private $security;
    private $authService;
    private $validator;

    public function __construct(AuthService $authService, LoggerInterface $logger, Security $security, ValidatorInterface $validator) {
        $this->logger = $logger;
        $this->security = $security;
        $this->authService = $authService;
        $this->validator = $validator;
    }

    #[Route('/', name: 'root', methods: ['GET'])]
    public function rootRedirect(): Response
    {
        return $this->redirectToRoute('app_view');
    }

    #[Route('/app', name: 'app_view', methods: ['GET'])]
    public function appView(): Response
    {
        return $this->render('dashboard/app.html.twig');
    }

    #[Route('/app/login', name: 'app_login_view', methods: ['GET'])]
    public function loginView(AuthenticationUtils $authenticationUtils): Response
    {
        // Obtener el error de login si existe
        $error = $authenticationUtils->getLastAuthenticationError();

        // Último nombre de usuario ingresado
        $lastUsername = $authenticationUtils->getLastUsername();

        $responseData = null;
        if ($error) {
            $responseData = [
                'success' => false,
                'message' => $this->translateError($error->getMessage()),
                'status' => Response::HTTP_UNAUTHORIZED
            ];
        } else {
            return $this->render('sessionManagement/login.html.twig');
        }

        return $this->render('sessionManagement/login.html.twig', [
            'last_username' => $lastUsername,
            'responseData' => $responseData
        ]);
    }

    #[Route('/app/login', name: 'app_login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        $email = trim($request->request->get('_username', ''));
        $password = $request->request->get('_password', '');

        // Validación mejorada de formato
        $validationErrors = $this->validateLoginData($email, $password);
        if (!empty($validationErrors)) {
            $this->logger->info('Intento de login con datos inválidos', [
                'email' => $email,
                'errors' => $validationErrors
            ]);
            
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => implode('. ', $validationErrors),
                    'status' => Response::HTTP_BAD_REQUEST,
                    'errors' => $validationErrors
                ]
            ]);
        }

        try {
            // Delegar la lógica de autenticación al servicio
            $user = $this->authService->login($email, $password);

            $this->security->login($user);

            // Log exitoso
            $this->logger->info('Login exitoso', [
                'user_id' => $user->getId(),
                'email' => $email
            ]);

            // Agregar mensaje de bienvenida
            $this->addFlash('success', '¡Bienvenido de nuevo, ' . $user->getName() . '!');

            // Redireccionar al dashboard
            return $this->redirectToRoute('app_view');

        } catch (InvalidCredentialsException $e) {
            $this->logger->warning('Intento de login fallido - Credenciales inválidas', [
                'email' => $email,
                'message' => $e->getMessage(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent')
            ]);
            
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos. Verifica tus credenciales e intenta nuevamente.',
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'error_type' => 'invalid_credentials'
                ]
            ]);

        } catch (UserBlockedException $e) {
            $this->logger->warning('Intento de login fallido - Usuario bloqueado', [
                'email' => $email,
                'message' => $e->getMessage(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent')
            ]);
            
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => 'Tu cuenta ha sido bloqueada. Contacta al administrador para más información.',
                    'status' => Response::HTTP_FORBIDDEN,
                    'error_type' => 'user_blocked'
                ]
            ]);

        } catch (\Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException $e) {
            $this->logger->warning('Demasiados intentos de login', [
                'email' => $email,
                'ip' => $request->getClientIp(),
                'message' => $e->getMessage()
            ]);
            
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => 'Demasiados intentos de login. Espera unos minutos antes de intentar nuevamente.',
                    'status' => Response::HTTP_TOO_MANY_REQUESTS,
                    'error_type' => 'too_many_attempts'
                ]
            ]);

        } catch (\Symfony\Component\Security\Core\Exception\UserNotFoundException $e) {
            $this->logger->info('Intento de login con usuario inexistente', [
                'email' => $email,
                'ip' => $request->getClientIp()
            ]);
            
            // Por seguridad, mostramos el mismo mensaje que para credenciales inválidas
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos. Verifica tus credenciales e intenta nuevamente.',
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'error_type' => 'invalid_credentials'
                ]
            ]);

        } catch (\Symfony\Component\Security\Core\Exception\DisabledException $e) {
            $this->logger->warning('Intento de login con cuenta deshabilitada', [
                'email' => $email,
                'ip' => $request->getClientIp()
            ]);
            
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => 'Tu cuenta está deshabilitada. Contacta al administrador.',
                    'status' => Response::HTTP_FORBIDDEN,
                    'error_type' => 'account_disabled'
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error interno en el login', [
                'email' => $email,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->getClientIp()
            ]);
            
            return $this->render('sessionManagement/login.html.twig', [
                'last_username' => $email,
                'responseData' => [
                    'success' => false,
                    'message' => 'Ocurrió un error interno. Por favor, intenta nuevamente en unos momentos.',
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'error_type' => 'internal_error'
                ]
            ]);
        }
    }

    #[Route('/app/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        // Aquí puedes manejar la lógica de logout si es necesario
        return $this->render('app/index.html.twig', [
            'responseData' => [
                'success' => true,
                'message' => 'Logout exitoso',
                'status'  => Response::HTTP_OK
            ]
        ]);
    }

    #[Route('/app/register', name: 'app_register_view', methods: ['GET'])]
    public function registerView(): Response
    {
        return $this->render('sessionManagement/register.html.twig');
    }

    #[Route('/app/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        // Extraer datos del request
        $name = $request->request->get('_username'); 
        $email = $request->request->get('_email'); 
        $password = $request->request->get('_password'); 

        // Validación básica de formato
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

            $this->security->login($user);

            return $this->render('dashboard/index.html.twig', [
                'responseData' => [
                    'success' => true,
                    'message' => 'Registro exitoso',
                    'user' => [
                        'id' => $user->getId(),
                        'username' => $user->getName(),
                        'email' => $user->getEmail()
                    ],
                    'status'  => Response::HTTP_CREATED
                ]
            ]);

        } catch (\Exception $e) {
            return $this->render('sessionManagement/register.html.twig', [
                'responseData' => [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'status'  => Response::HTTP_INTERNAL_SERVER_ERROR
                ]
            ]);
        }
    }

    /**
     * Valida los datos de login
     */
    private function validateLoginData(string $email, string $password): array
    {
        $errors = [];

        // Validar email
        if (empty($email)) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El formato del email no es válido';
        } elseif (strlen($email) > 254) {
            $errors[] = 'El email es demasiado largo';
        }

        // Validar password
        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 3) {
            $errors[] = 'La contraseña debe tener al menos 3 caracteres';
        } elseif (strlen($password) > 4096) {
            $errors[] = 'La contraseña es demasiado larga';
        }

        return $errors;
    }

    /**
     * Traduce los errores de autenticación a mensajes más amigables
     */
    private function translateError(string $error): string
    {
        switch ($error) {
            case 'Invalid credentials.':
                return 'Email o contraseña incorrectos';
            case 'Bad credentials.':
                return 'Credenciales inválidas';
            case 'Username could not be found.':
                return 'Usuario no encontrado';
            case 'Too many failed login attempts.':
                return 'Demasiados intentos fallidos. Intenta más tarde';
            case 'Account is disabled.':
                return 'La cuenta está deshabilitada';
            case 'Account is locked.':
                return 'La cuenta está bloqueada';
            default:
                return 'Error de autenticación';
        }
    }
}
