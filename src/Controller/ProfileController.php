<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class ProfileController extends AbstractController {

	#[Route('/app/profile', name: 'profile')]
	public function profile()
	{
		$user = $this->getUser();
		if (!$user) {
			throw $this->createAccessDeniedException('You are not logged in.');
		}

		return $this->render('profile/profile.html.twig', [
			'user' => $user,
		]);
	}
}