<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\ProfileService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController {

	private ProfileService $profileService;
	private UserPasswordHasherInterface $passwordHasher;

	public function __construct(ProfileService $profileService, UserPasswordHasherInterface $passwordHasher)
	{
		$this->profileService = $profileService;
		$this->passwordHasher = $passwordHasher;
	}
	
	#[Route('/app/profile', name: 'profile')]
	public function profile(): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		$user = $this->getUser();
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		// Obtener datos del perfil usando el servicio
		$profileData = $this->profileService->getProfileData($user);

		return $this->render('sessionManagement/profile.html.twig', [
			'user' => $user,
			'profileData' => $profileData
		]);
	}

	#[Route('/app/profile_update', name: 'profile_update', methods: ['POST'])]
	public function updateProfile(Request $request): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		$user = $this->getUser();
		
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		try {
			// Obtener datos del formulario
			$profileData = [
				'name' => $request->request->get('name'),
				'email' => $request->request->get('email')
			];

			// Actualizar perfil usando el servicio
			$this->profileService->updateProfile($user, $profileData);

			$this->addFlash('success', 'Perfil actualizado correctamente');

		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}

		return $this->redirectToRoute('profile');
	}

	#[Route('/app/profile_change_password', name: 'profile_change_password', methods: ['POST'])]
	public function changePassword(Request $request): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		$user = $this->getUser();
		
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		try {
			$currentPassword = $request->request->get('current_password');
			$newPassword = $request->request->get('new_password');
			$confirmPassword = $request->request->get('confirm_password');

			// Validar que las contraseñas coincidan
			if ($newPassword !== $confirmPassword) {
				throw new \Exception('Las contraseñas no coinciden');
			}

			// Validar longitud mínima
			if (strlen($newPassword) < 6) {
				throw new \Exception('La contraseña debe tener al menos 6 caracteres');
			}

			// Cambiar contraseña usando el servicio
			$this->profileService->changePassword($user, $currentPassword, $newPassword);

			$this->addFlash('success', 'Contraseña cambiada correctamente');

		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
		}

		return $this->redirectToRoute('profile');
	}

	#[Route('/app/profile_upload_picture', name: 'profile_upload_picture', methods: ['POST'])]
	public function uploadProfilePicture(Request $request): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		$user = $this->getUser();
		
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		try {
			/** @var UploadedFile $uploadedFile */
			$uploadedFile = $request->files->get('profile_picture');
			
			if (!$uploadedFile) {
				throw new \Exception('No se ha seleccionado ningún archivo');
			}

			// Subir foto usando el servicio
			$picturePath = $this->profileService->uploadProfilePicture($user, $uploadedFile);

			$this->addFlash('success', 'Foto de perfil actualizada correctamente');

			// Si es una petición AJAX, devolver JSON
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse([
					'success' => true,
					'message' => 'Foto de perfil actualizada correctamente',
					'picturePath' => '/' . $picturePath
				]);
			}

		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());

			// Si es una petición AJAX, devolver error en JSON
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse([
					'success' => false,
					'message' => $e->getMessage()
				], 400);
			}
		}

		return $this->redirectToRoute('profile');
	}

	#[Route('/app/profile_remove_picture', name: 'profile_remove_picture', methods: ['POST'])]
	public function removeProfilePicture(Request $request): Response
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		$user = $this->getUser();
		
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		try {
			// Eliminar foto usando el servicio
			$this->profileService->removeProfilePicture($user);

			$this->addFlash('success', 'Foto de perfil eliminada correctamente');

			// Si es una petición AJAX, devolver JSON
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse([
					'success' => true,
					'message' => 'Foto de perfil eliminada correctamente'
				]);
			}

		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());

			// Si es una petición AJAX, devolver error en JSON
			if ($request->isXmlHttpRequest()) {
				return new JsonResponse([
					'success' => false,
					'message' => $e->getMessage()
				], 400);
			}
		}

		return $this->redirectToRoute('profile');
	}

	#[Route('/app/profile_delete', name: 'profile_delete', methods: ['POST'])]
	public function deleteProfile(Request $request, Security $security): Response
	{
		$user = $this->getUser();
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		$this->denyAccessUnlessGranted('ROLE_USER');

		try {
			// Validar contraseña antes de eliminar
			$password = $request->request->get('password');
			if (!$password) {
				throw new \Exception('Debe confirmar su contraseña para eliminar la cuenta');
			}

			// Verificar contraseña
			if (!$this->passwordHasher->isPasswordValid($user, $password)) {
				throw new \Exception('Contraseña incorrecta');
			}

			// Cerrar sesión antes de eliminar
			$security->logout(false);

			// Eliminar perfil usando el servicio
			$this->profileService->deleteProfile($user);

			// Invalidar sesión
			$request->getSession()->invalidate();

			$this->addFlash('success', 'Su cuenta ha sido eliminada correctamente');

		} catch (\Exception $e) {
			$this->addFlash('error', $e->getMessage());
			return $this->redirectToRoute('profile');
		}

		return $this->redirectToRoute('app_logout');
	}
}
