<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;

class ProfileController extends AbstractController {

	private UserRepository $userRepository;

	// Construct
	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}
	
	#[Route('/app/profile', name: 'profile')]
	public function profile()
	{
		$this->denyAccessUnlessGranted('ROLE_USER');
		$user = $this->getUser();
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		return $this->render('sessionManagement/profile.html.twig');
	}

	#[Route('/app/profile_update', name: 'profile_update', methods: ['POST'])]
	public function updateProfile()
	{
		$this->denyAccessUnlessGranted('ROLE_USER');

		// TODO hacer el update del perfil del usuario

		return $this->redirectToRoute('profile');
	}

	// Eliminar cuenta de usuairo
	#[Route('/app/profile_delete', name: 'profile_delete', methods: ['POST'])]
	public function deleteProfile()
	{
		$this->denyAccessUnlessGranted('ROLE_USER');

		$user = $this->getUser();
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		// Eliminar el usuario
		$this->userRepository->remove($user, true);

		return $this->redirectToRoute('app_logout');
	}
}
