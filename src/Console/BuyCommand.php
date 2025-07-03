<?php
declare(strict_types=1);

/**
 * File: BuyCommand.php
 *
 * @author Bartosz Juszczyk <b.juszczyk@bjuszczyk.pl>
 * @copyright Copyright (C) 2025
 */

namespace Juszczyk\Console;

use G2A\IntegrationApi\Client;
use G2A\IntegrationApi\Model\Config;
use Juszczyk\Api\G2AProductBuyer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuyCommand extends Command
{
    private const string PRODUCT_OPTION = 'product';
    private const string INVENTORY_OPTION = 'inventory';
    private const string QTY_OPTION = 'qty';
    private const string API_EMAIL = 'sandboxapitest@g2a.com';
    private const string API_DOMAIN = 'sandboxapi.g2a.com';
    private const string API_HASH = 'qdaiciDiyMaTjxMt';
    private const string API_CUSTOMER_SECRET = 'b0d293f6-e1d2-4629-8264-fd63b5af3207b0d293f6-e1d2-4629-8264-fd63b5af3207';

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('buy')
            ->setDescription('Buy products and get their keys from G2A.')
            ->addOption(
                self::INVENTORY_OPTION,
                'i',
                InputOption::VALUE_OPTIONAL,
                'G2A Inventory.',
                'g2a'
            )->addOption(
                self::PRODUCT_OPTION,
                'p',
                InputOption::VALUE_REQUIRED,
                'Product ID.'
            )->addOption(
                self::QTY_OPTION,
                null,
                InputOption::VALUE_OPTIONAL,
                'Product Quantity to buy.',
                1
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $apiClient = $this->createApiClient();
            $g2aProductBuyer = new G2AProductBuyer();

            [$productId, $qty] = $this->resolveOptions($input);

            $orderIds = $g2aProductBuyer->createOrders($apiClient, $output, $productId, $qty);
            $g2aProductBuyer->payForOrders($apiClient, $output, $orderIds);
            $keys = $g2aProductBuyer->getKeysFromOrders($apiClient, $output, $orderIds);

            $this->showOutput($keys, $output);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * @return Client
     */
    private function createApiClient(): Client
    {
        $config = new Config(
            self::API_EMAIL,
            self::API_DOMAIN,
            self::API_HASH,
            self::API_CUSTOMER_SECRET
        );

        return new Client($config);
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function resolveOptions(InputInterface $input): array
    {
        return [$input->getOption(self::PRODUCT_OPTION), (int) $input->getOption(self::QTY_OPTION)];
    }

    /**
     * @param array $keys
     * @param OutputInterface $output
     * @return void
     */
    private function showOutput(array $keys, OutputInterface $output): void
    {
        foreach ($keys as $key) {
            $output->writeln($key);
        }
    }
}