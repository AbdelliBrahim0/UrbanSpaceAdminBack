<?php

namespace App\Controller;


    use App\Entity\Category;
    use App\Repository\CategoryRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Serializer\SerializerInterface;
    use Symfony\Component\Validator\Validator\ValidatorInterface;

    #[Route('/api/categories', name: 'api_categories_')]
    class CategoryController extends AbstractController
    {
        public function __construct(
            private EntityManagerInterface $entityManager,
            private SerializerInterface $serializer,
            private ValidatorInterface $validator
        ) {}

        #[Route('', name: 'list', methods: ['GET'])]
        public function list(Request $request, CategoryRepository $categoryRepository): JsonResponse
        {
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 10);
            $search = $request->query->get('search', '');
            $activeOnly = $request->query->getBoolean('active_only', false);

            if ($search) {
                $categories = $categoryRepository->findBySearchTerm($search, $activeOnly);
                $total = count($categories);
            } else {
                $paginator = $categoryRepository->findWithPagination($page, $limit, $activeOnly);
                $categories = $paginator->getIterator();
                $total = count($paginator);
            }

            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category->getId(),
                    'categorie_parent_id' => $category->getCategorieParent() ? $category->getCategorieParent()->getId() : null,
                    'nom' => $category->getNom(),
                    'description' => $category->getDescription(),
                ];
            }

            return $this->json($data, Response::HTTP_OK);
        }

        #[Route('/hierarchy', name: 'hierarchy', methods: ['GET'])]
        public function hierarchy(CategoryRepository $categoryRepository): JsonResponse
        {
            $hierarchy = $categoryRepository->findHierarchy();
            return $this->json($hierarchy, Response::HTTP_OK, [], ['groups' => 'category:read']);
        }

        #[Route('/parents', name: 'parents', methods: ['GET'])]
        public function parents(CategoryRepository $categoryRepository): JsonResponse
        {
            $parents = $categoryRepository->findParentCategories();
            return $this->json($parents, Response::HTTP_OK, [], ['groups' => 'category:list']);
        }

        #[Route('/{id}', name: 'show', methods: ['GET'])]
        public function show(Category $category): JsonResponse
        {
            $data = [
                'id' => $category->getId(),
                'categorie_parent_id' => $category->getCategorieParent() ? $category->getCategorieParent()->getId() : null,
                'nom' => $category->getNom(),
                'description' => $category->getDescription(),
            ];
            return $this->json($data, Response::HTTP_OK);
        }

        #[Route('/slug/{slug}', name: 'show_by_slug', methods: ['GET'])]
        public function showBySlug(string $slug, CategoryRepository $categoryRepository): JsonResponse
        {
            $category = $categoryRepository->findBySlug($slug);

            if (!$category) {
                return $this->json(['error' => 'Catégorie non trouvée'], Response::HTTP_NOT_FOUND);
            }

            return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
        }

        #[Route('', name: 'create', methods: ['POST'])]
        public function create(Request $request, CategoryRepository $categoryRepository): JsonResponse
        {
            $data = json_decode($request->getContent(), true);

            $category = new Category();
            $category->setNom($data['nom'] ?? '');
            $category->setDescription($data['description'] ?? null);

            // Gérer la catégorie parent via categorie_parent_id
            if (isset($data['categorie_parent_id']) && $data['categorie_parent_id']) {
                $parent = $categoryRepository->find($data['categorie_parent_id']);
                if ($parent) {
                    $category->setCategorieParent($parent);
                }
            }

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $data = [
                'id' => $category->getId(),
                'categorie_parent_id' => $category->getCategorieParent() ? $category->getCategorieParent()->getId() : null,
                'nom' => $category->getNom(),
                'description' => $category->getDescription(),
            ];
            return $this->json($data, Response::HTTP_CREATED);
        }

        #[Route('/{id}', name: 'update', methods: ['PUT'])]
        public function update(Request $request, Category $category, CategoryRepository $categoryRepository): JsonResponse
        {
            $data = json_decode($request->getContent(), true);

            if (isset($data['nom'])) {
                $category->setNom($data['nom']);
            }
            if (isset($data['description'])) {
                $category->setDescription($data['description']);
            }
            // Gérer la catégorie parent via categorie_parent_id
            if (isset($data['categorie_parent_id'])) {
                if ($data['categorie_parent_id']) {
                    $parent = $categoryRepository->find($data['categorie_parent_id']);
                    if ($parent && $parent->getId() !== $category->getId()) {
                        $category->setCategorieParent($parent);
                    }
                } else {
                    $category->setCategorieParent(null);
                }
            }

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            $data = [
                'id' => $category->getId(),
                'categorie_parent_id' => $category->getCategorieParent() ? $category->getCategorieParent()->getId() : null,
                'nom' => $category->getNom(),
                'description' => $category->getDescription(),
            ];
            return $this->json($data, Response::HTTP_OK);
        }

        #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
        public function delete(Category $category): JsonResponse
        {
            // Vérifier s'il y a des sous-catégories
            if ($category->hasChildren()) {
                return $this->json([
                    'error' => 'Impossible de supprimer une catégorie qui contient des sous-catégories'
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->remove($category);
            $this->entityManager->flush();

            return $this->json(['message' => 'Catégorie supprimée avec succès'], Response::HTTP_OK);
        }

        #[Route('/toggle/{id}', name: 'toggle', methods: ['PATCH'])]
        public function toggleActive(Category $category): JsonResponse
        {
            $category->setActif(!$category->isActif());
            $this->entityManager->flush();

            return $this->json($category, Response::HTTP_OK, [], ['groups' => 'category:read']);
        }

        #[Route('/reorder', name: 'reorder', methods: ['PUT'])]
        public function reorder(Request $request, CategoryRepository $categoryRepository): JsonResponse
        {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['categories']) || !is_array($data['categories'])) {
                return $this->json(['error' => 'Format de données invalide'], Response::HTTP_BAD_REQUEST);
            }

            foreach ($data['categories'] as $categoryData) {
                if (isset($categoryData['id']) && isset($categoryData['ordre'])) {
                    $category = $categoryRepository->find($categoryData['id']);
                    if ($category) {
                        $category->setOrdre($categoryData['ordre']);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json(['message' => 'Ordre mis à jour avec succès'], Response::HTTP_OK);
        }

        #[Route('/stats', name: 'stats', methods: ['GET'])]
        public function stats(CategoryRepository $categoryRepository): JsonResponse
        {
            $totalCategories = $categoryRepository->countTotal();
            $activeCategories = $categoryRepository->countTotal(true);
            $parentCategories = count($categoryRepository->findParentCategories());

            $recentCategories = $this->entityManager->createQuery(
                'SELECT COUNT(c.id) FROM App\Entity\Category c WHERE c.dateCreation >= :date'
            )
                ->setParameter('date', new \DateTimeImmutable('-30 days'))
                ->getSingleScalarResult();

            return $this->json([
                'totalCategories' => $totalCategories,
                'activeCategories' => $activeCategories,
                'inactiveCategories' => $totalCategories - $activeCategories,
                'parentCategories' => $parentCategories,
                'recentCategories' => $recentCategories
            ]);
        }
    }