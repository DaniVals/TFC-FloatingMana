<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class ProfileController extends AbstractController {
	
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
}