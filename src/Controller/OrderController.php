<?php

namespace App\Controller;

use App\DTO\Response\OrderDto;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class OrderController extends AbstractController
{
    const SHOW_ORDER_ITEMS = true;
    const DONT_SHOW_ORDER_ITEMS = false;
    #[Route('/order', name: 'app_order')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/OrderController.php',
        ]);
    }
    #[Route('/orders', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = $request->toArray();

        $order = new Order();
        $order->setCustomer($this->getUser());
        $order->setAddress($data['address']);

        $entityManager->persist($order);

        foreach ($data['orderItems'] as $dataItem) {
            $product = $entityManager->getRepository(Product::class)->find($dataItem['productId']);
            if (!$product) {
                return new JsonResponse(['message' => "Product ".$dataItem['productId'].' not found'], Response::HTTP_NOT_FOUND);
            }

            $orderItem = new OrderItem();
            $orderItem->setProductId($product);
            $orderItem->setQuantity($dataItem['quantity']);
            $orderItem->setPrice($product->getPrice());
            $orderItem->setOrderId($order);

            $entityManager->persist($orderItem);
        }

        $entityManager->flush();

        return $this->json(['id' => $order->getId()], Response::HTTP_CREATED);
    }

    #[Route('/orders', name: 'orders_list', methods: ['GET'])]
    public function orderList(EntityManagerInterface $entityManager): JsonResponse
    {
        $orders = $entityManager->getRepository(Order::class)->findBy([], ['id' => 'DESC'], 10);

        $ordersDtos = [];
        foreach ($orders as $order) {
            $ordersDtos[] = $this->createOrderDto($order, self::DONT_SHOW_ORDER_ITEMS);
        }

        return new JsonResponse($ordersDtos);
    }

    #[Route('/orders/{id}', name: 'get_order', requirements: ['page' => '\d+'], methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            return new JsonResponse(['message' => "Order ".$id.' not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->createOrderDto($order));
    }

    private function createOrderDto(mixed $order, $showOrderItems = self::SHOW_ORDER_ITEMS): OrderDto
    {
        $orderDto = new OrderDto();
        $orderDto->id = $order->getId();
        $orderDto->address = $order->getAddress();
        $orderDto->user = $order->getCustomer()->getEmail();
        if ($showOrderItems) {
            $orderDto->orderItems = [];
        }

        $total = 0;
        foreach ($order->getOrderItems() as $orderItem) {
            if ($showOrderItems) {
                $orderDto->orderItems[] = [
                    'productId' => $orderItem->getProductId()->getId(),
                    'quantity' => $orderItem->getQuantity(),
                    'price' => $orderItem->getPrice(),
                ];
            }
            $total = $total + $orderItem->getPrice() * $orderItem->getQuantity();
        }

        $orderDto->total = $total/100;

        return $orderDto;
    }
}
