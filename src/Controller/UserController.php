<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $search = $request->query->get('search', '');

        if ($search) {
            $users = $userRepository->findBySearchTerm($search);
            $total = count($users);
        } else {
            $paginator = $userRepository->findWithPagination($page, $limit);
            $users = $paginator->getIterator();
            $total = count($paginator);
        }

        $data = [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ];

        return $this->json($data, Response::HTTP_OK, [], ['groups' => 'user:list']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setNom($data['nom'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setTelephone($data['telephone'] ?? '');
        $user->setAdresse($data['adresse'] ?? '');
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        if (isset($data['provider'])) {
            $user->setProvider($data['provider']);
        }
        if (isset($data['providerId'])) {
            $user->setProviderId($data['providerId']);
        }
        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['telephone'])) {
            $user->setTelephone($data['telephone']);
        }
        if (isset($data['adresse'])) {
            $user->setAdresse($data['adresse']);
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        if (isset($data['provider'])) {
            $user->setProvider($data['provider']);
        }
        if (isset($data['providerId'])) {
            $user->setProviderId($data['providerId']);
        }
        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'Utilisateur supprimé avec succès'], Response::HTTP_OK);
    }

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(UserRepository $userRepository): JsonResponse
    {
        $totalUsers = $userRepository->countTotal();

        $oauthUsers = $this->entityManager->createQuery(
            'SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.provider IS NOT NULL'
        )->getSingleScalarResult();

        $regularUsers = $totalUsers - $oauthUsers;

        $recentUsers = $this->entityManager->createQuery(
            'SELECT COUNT(u.id) FROM App\Entity\User u WHERE u.dateInscription >= :date'
        )
            ->setParameter('date', new \DateTimeImmutable('-30 days'))
            ->getSingleScalarResult();

        return $this->json([
            'totalUsers' => $totalUsers,
            'oauthUsers' => $oauthUsers,
            'regularUsers' => $regularUsers,
            'recentUsers' => $recentUsers
        ]);
    }
}