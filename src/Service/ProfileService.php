<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileService {
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private SluggerInterface $slugger;
    private string $publicDirectory;
    
    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger,
        ?string $publicDirectory = null
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
        $this->publicDirectory = $publicDirectory ?: __DIR__ . '/../../public';
    }
    
    /**
     * Actualiza los datos básicos del perfil del usuario
     */
    public function updateProfile(User $user, array $data): User {
        // Actualizar nombre si se proporciona
        if (isset($data['name']) && !empty(trim($data['name']))) {
            $user->setName(trim($data['name']));
        }
        
        // Actualizar email si se proporciona y es diferente
        if (isset($data['email']) && !empty(trim($data['email']))) {
            $newEmail = trim($data['email']);
            if ($newEmail !== $user->getEmail()) {
                // Verificar que el nuevo email no esté en uso
                $existingUser = $this->userRepository->findOneByEmail($newEmail);
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    throw new \Exception('Este email ya está registrado por otro usuario');
                }
                
                // Renombrar directorio de fotos de perfil
                $this->renameUserDirectory($user->getEmail(), $newEmail);
                $user->setEmail($newEmail);
            }
        }
        
        // Guardar cambios
        $this->userRepository->save($user, true);
        
        return $user;
    }
    
    /**
     * Cambia la contraseña del usuario
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void {
        // Verificar contraseña actual
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new \Exception('La contraseña actual es incorrecta');
        }
        
        // Hashear nueva contraseña
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        
        // Guardar cambios
        $this->userRepository->save($user, true);
    }
    
    /**
     * Maneja la subida de foto de perfil
     */
    public function uploadProfilePicture(User $user, UploadedFile $file): string {
        // Validar el archivo
        $this->validateProfilePicture($file);
        
        // Obtener directorio del usuario
        $userDirectory = $this->getUserDirectory($user->getEmail());
        
        // Generar nombre único para el archivo
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $extension = $file->guessExtension();
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
        
        // Eliminar foto anterior si existe
        $this->removeOldProfilePicture($user);
        
        // Mover archivo al directorio del usuario
        $file->move($userDirectory, $newFilename);
        
        // Actualizar ruta en la base de datos
        $relativePath = 'profilePictures/' . $user->getEmail() . '/' . $newFilename;
        $user->setProfilePicture($relativePath);
        $this->userRepository->save($user, true);
        
        return $relativePath;
    }
    
    /**
     * Elimina la foto de perfil actual
     */
    public function removeProfilePicture(User $user): void {
        $this->removeOldProfilePicture($user);
        $user->setProfilePicture(null);
        $this->userRepository->save($user, true);
    }
    
    /**
     * Obtiene la información completa del perfil
     */
    public function getProfileData(User $user): array {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'profilePicture' => $user->getProfilePicture(),
            'roles' => $user->getRoles(),
            'isActive' => $user->isActive(),
            'failedLoginAttempts' => $user->getFailedLoginAttempts(),
            // 'createdAt' => $user->getCreatedAt(),
            // 'lastLoginAt' => $user->getLastLoginAt(),
        ];
    }
    
    /**
     * Elimina completamente el perfil del usuario
     */
    public function deleteProfile(User $user): void {
        // Eliminar directorio de fotos
        $this->removeUserDirectory($user->getEmail());
        
        // Eliminar usuario de la base de datos
        $this->userRepository->remove($user, true);
    }
    
    /**
     * Valida el archivo de foto de perfil
     */
    private function validateProfilePicture(UploadedFile $file): void {
        // Verificar tamaño (máximo 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('El archivo es demasiado grande. Máximo 5MB permitido.');
        }
        
        // Verificar tipo MIME
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('Tipo de archivo no permitido. Solo se permiten imágenes (JPEG, PNG, GIF, WebP).');
        }
    }
    
    /**
     * Obtiene el directorio del usuario para las fotos de perfil
     */
    private function getUserDirectory(string $email): string {
        $userDirectory = $this->publicDirectory . '/profilePictures/' . $email;
        
        if (!is_dir($userDirectory)) {
            mkdir($userDirectory, 0777, true);
        }
        
        return $userDirectory;
    }
    
    /**
     * Elimina la foto de perfil anterior
     */
    private function removeOldProfilePicture(User $user): void {
        $oldPicture = $user->getProfilePicture();
        if ($oldPicture) {
            $oldPicturePath = $this->publicDirectory . '/' . $oldPicture;
            if (file_exists($oldPicturePath)) {
                unlink($oldPicturePath);
            }
        }
    }
    
    /**
     * Renombra el directorio del usuario cuando cambia el email
     */
    private function renameUserDirectory(string $oldEmail, string $newEmail): void {
        $oldDirectory = $this->publicDirectory . '/profilePictures/' . $oldEmail;
        $newDirectory = $this->publicDirectory . '/profilePictures/' . $newEmail;
        
        if (is_dir($oldDirectory)) {
            rename($oldDirectory, $newDirectory);
        }
    }
    
    /**
     * Elimina completamente el directorio del usuario
     */
    private function removeUserDirectory(string $email): void {
        $userDirectory = $this->publicDirectory . '/profilePictures/' . $email;
        
        if (is_dir($userDirectory)) {
            // Eliminar todos los archivos del directorio
            $files = glob($userDirectory . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            // Eliminar el directorio
            rmdir($userDirectory);
        }
    }
}
