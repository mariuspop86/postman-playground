<?php

namespace App\DTO\Response;

class OrderDto
{
    public int $id;
    public string $address;
    public ?string $user;
    public ?array $orderItems;
    public float $total;

}
