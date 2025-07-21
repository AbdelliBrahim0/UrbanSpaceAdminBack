<?php

namespace App\Controller\admin;

use App\Entity\Admin\Product;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, ProductRepository $productRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 12);
        $search = $request->query->get('search', '');
        $categoryId = $request->query->get('category');

        $qb = $productRepository->createQueryBuilder('p');
        if ($search) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
                ->setParameter('search', "%$search%");
        }
        $categoryTitle = null;
        if ($categoryId) {
            $qb->andWhere('p.categorie = :categoryId')
                ->setParameter('categoryId', $categoryId);
            // Récupérer le nom de la catégorie
            $category = $this->entityManager->getRepository('App\\Entity\\Admin\\Category')->find($categoryId);
            if ($category) {
                $categoryTitle = $category->getNom();
            }
        }
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        $products = $qb->getQuery()->getResult();
        $total = $productRepository->count([]);

        $data = [
            'products' => $products,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ];
        if ($categoryTitle) {
            $data['categoryTitle'] = $categoryTitle;
        }
        return $this->json($data, Response::HTTP_OK, [], ['groups' => 'product:list']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->json($product, Response::HTTP_OK, [], ['groups' => 'product:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CategoryRepository $categoryRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setNom($data['nom'] ?? '');
        $product->setDescription($data['description'] ?? null);
        $product->setPrix($data['prix'] ?? 0);
        $product->setTailles($data['tailles'] ?? []);
        $product->setCouleurs($data['couleurs'] ?? []);
        $product->setStock($data['stock'] ?? 0);
        $product->setImageUrl($data['imageUrl'] ?? null);

        // Gestion de la catégorie
        if (isset($data['categorie_id'])) {
            $categorie = $categoryRepository->find($data['categorie_id']);
            if (!$categorie) {
                return $this->json(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
            }
            $product->setCategorie($categorie);
        } else {
            return $this->json(['error' => 'Catégorie obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();
        return $this->json($product, Response::HTTP_CREATED, [], ['groups' => 'product:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Product $product, CategoryRepository $categoryRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['nom'])) $product->setNom($data['nom']);
        if (isset($data['description'])) $product->setDescription($data['description']);
        if (isset($data['prix'])) $product->setPrix($data['prix']);
        if (isset($data['tailles'])) $product->setTailles($data['tailles']);
        if (isset($data['couleurs'])) $product->setCouleurs($data['couleurs']);
        if (isset($data['stock'])) $product->setStock($data['stock']);
        if (isset($data['imageUrl'])) $product->setImageUrl($data['imageUrl']);
        if (isset($data['categorie_id'])) {
            $categorie = $categoryRepository->find($data['categorie_id']);
            if (!$categorie) {
                return $this->json(['error' => 'Catégorie non trouvée'], Response::HTTP_BAD_REQUEST);
            }
            $product->setCategorie($categorie);
        }
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $this->entityManager->flush();
        return $this->json($product, Response::HTTP_OK, [], ['groups' => 'product:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Product $product): Response
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        return $this->json(['message' => 'Produit supprimé avec succès'], Response::HTTP_OK);
    }
}
