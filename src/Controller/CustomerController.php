<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
    #[Route('/customers', name: 'customer_list', methods: ['GET'])]
    public function listCustomers(CustomerRepository $customerRepository): Response
    {
        $customers = $customerRepository->findAll();
        return $this->json($customers, Response::HTTP_OK, [], ['groups' => 'customer_details']);
    }

    #[Route('/customers/{id}/orders', name: 'customer_orders', methods: ['GET'])]
    public function customerOrders(int $id, CustomerRepository $customerRepository): Response
    {
        $customer = $customerRepository->find($id);
        if (!$customer) {
            return $this->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($customer->getOrders(), Response::HTTP_OK, [], ['groups' => 'order_details']);
    }
}
