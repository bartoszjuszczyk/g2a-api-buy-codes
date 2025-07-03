<?php

/**
 * File: G2AProductBuyer.php
 *
 * @author Bartosz Juszczyk <b.juszczyk@bjuszczyk.pl>
 * @copyright Copyright (C) 2025
 */

namespace Juszczyk\Api;

use G2A\IntegrationApi\Client;
use G2A\IntegrationApi\Request\OrderAddRequest;
use G2A\IntegrationApi\Request\OrderKeyRequest;
use G2A\IntegrationApi\Request\OrderPaymentRequest;
use Symfony\Component\Console\Output\OutputInterface;

class G2AProductBuyer
{
    /**
     * @param Client $apiClient
     * @param OutputInterface $output
     * @param string $productId
     * @param int $qty
     * @return array
     */
    public function createOrders(Client $apiClient, OutputInterface $output, string $productId, int $qty = 1): array
    {
        $orderIds = [];
        while ($qty > 0) {
            $request = new OrderAddRequest($apiClient);
            try {
                $request->setProductId($productId)->call();
                $orderIds[] = $request->getResponse()->getOrderId();
            } catch (\Throwable $e) {
                if ($request->getResponse() && $request->getResponse()->getStatus() !== '200') {
                    $message = sprintf(
                        "[API ERROR] Cannot create order for '%s' product: %s",
                        $productId,
                        $request->getResponse()->getMessage()
                    );
                } else {
                    $message = sprintf(
                        "[APP ERROR] Cannot create order for '%s' product: %s",
                        $productId,
                        $e->getMessage()
                    );
                }
                $this->handleException($output, $message);
            }
            --$qty;
        }

        return $orderIds;
    }

    /**
     * @param Client $apiClient
     * @param OutputInterface $output
     * @param array $orderIds
     * @return void
     */
    public function payForOrders(Client $apiClient, OutputInterface $output, array $orderIds): void
    {
        foreach ($orderIds as $orderId) {
            $request = new OrderPaymentRequest($apiClient);
            try {
                $request->setOrderId($orderId)->call();
            } catch (\Throwable $e) {
                if ($request->getResponse() && $request->getResponse()->getStatus() !== '200') {
                    $message = sprintf(
                        "[API ERROR] Cannot pay for order '%s': %s",
                        $orderId,
                        $request->getResponse()->getMessage()
                    );
                } else {
                    $message = sprintf(
                        "[APP ERROR] Cannot pay for order '%s': %s",
                        $orderId,
                        $e->getMessage()
                    );
                }
                $this->handleException($output, $message);
            }
        }
    }

    /**
     * @param Client $apiClient
     * @param OutputInterface $output
     * @param array $orderIds
     * @return array
     */
    public function getKeysFromOrders(Client $apiClient, OutputInterface $output, array $orderIds): array
    {
        $keys = [];
        foreach ($orderIds as $orderId) {
            $request = new OrderKeyRequest($apiClient);
            try {
                $request->setOrderId($orderId)->call();
                $keys[] = $request->getResponse()->getKey();
            } catch (\Throwable $e) {
                if ($request->getResponse() && $request->getResponse()->getStatus() !== '200') {
                    $message = sprintf(
                        "[API ERROR] Cannot get keys for order '%s': %s",
                        $orderId,
                        $request->getResponse()->getMessage()
                    );
                } else {
                    $message = sprintf(
                        "[APP ERROR] Cannot get keys for order '%s': %s",
                        $orderId,
                        $e->getMessage()
                    );
                }
                $this->handleException($output, $message);
            }
        }

        return $keys;
    }

    private function handleException(OutputInterface $output, string $message = ''): void
    {
        $output->writeln(sprintf("<error>%s</error>", $message));
    }
}