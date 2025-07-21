<?php

namespace App\Controller\admin;

use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiStatsController extends AbstractController
{
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function stats(
        UserRepository $userRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        $users = $userRepository->count([]);
        $products = $productRepository->count([]);
        $categories = $categoryRepository->count([]);

        return $this->json([
            'users' => $users,
            'products' => $products,
            'categories' => $categories
        ]);
    }
} 