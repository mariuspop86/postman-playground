<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader): JsonResponse
    {
        $data = $request->request->all();
        $files = $request->files->all();
        $file = $fileUploader->upload($files['avatar']);

        $user = new User();
        $plaintextPassword = $data['password'];
        $role = isset($data['role']) && $data['role'] == 1 ? User::ROLE_ADMIN : User::ROLE_USER;
        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $user->setRoles([$role]);
        $user->setEmail($data['email']);
        $user->setAvatar($file);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $user->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'get_current_user', methods: ['GET'])]
    public function getCurrentUser(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($user->toArray());
    }
}
