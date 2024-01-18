<?php

namespace App\Controller;

use App\DTO\Response\ProductDto;
use App\Entity\Product;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ProductController extends AbstractController
{
    #[Route('/products', name: 'create_product', methods: ['POST'])]
    public function createProduct(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = $request->toArray();
        $product = new Product();

        $this->hydrateProduct($product, $data);

        $entityManager->persist($product);

        $entityManager->flush();

        return new JsonResponse(['id' => $product->getId()], Response::HTTP_CREATED);
    }

    #[Route('/products/{id}', name: 'get_product', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['message' => "Product ".$id.' not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->createProductDto($product));
    }

    #[Route('/products/{id}', name: 'product_edit', requirements: ['page' => '\d+'], methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['message' => "Product ".$id.' not found'], Response::HTTP_NOT_FOUND);
        }
        $data = $request->toArray();

        $this->hydrateProduct($product, $data);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Product '.$id.' updated']);
    }

    #[Route('/products/{id}', name: 'product_delete', requirements: ['page' => '\d+'], methods: ['DELETE'])]
    public function remove(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['message' => "Product ".$id.' not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product '.$id.' deleted']);
    }

    #[Route('/products', name: 'get_product_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = $request->query->get('q');

        if (!empty($data)) {
            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->contains('name', $data))->setMaxResults(10);
            $products = $entityManager->getRepository(Product::class)->matching($criteria);
        } else {
            $products = $entityManager->getRepository(Product::class)->findBy([], ['id' => 'DESC'], 10);
        }

        $productDtos = [];
        foreach ($products as $product) {
            $productDtos[] = $this->createProductDto($product);;
        }

        return new JsonResponse($productDtos);
    }

    private function createProductDto(Product $product): ProductDto
    {
        $productDto = new ProductDto();
        $productDto->id = $product->getId();
        $productDto->name = $product->getName();
        $productDto->price = $product->getPrice()/100;
        $productDto->description = $product->getDescription();

        return $productDto;
    }

    private function hydrateProduct(Product $product, array $data): void
    {
        $product->setName($data['name']);
        $product->setPrice($data['price']*100);
        $product->setDescription($data['description']);
    }
}
