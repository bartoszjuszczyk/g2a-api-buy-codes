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
use G2A\IntegrationApi\Exception\Model\InvalidConfigException;
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
            $paidOrderIds = $g2aProductBuyer->payForOrders($apiClient, $output, $orderIds);
            $keys = $g2aProductBuyer->getKeysFromOrders($apiClient, $output, $paidOrderIds);

            $this->showOutput($keys, $output);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * @return Client
     * @throws InvalidConfigException
     */
    private function createApiClient(): Client
    {
        $config = new Config(
            $_ENV['API_EMAIL'],
            $_ENV['API_DOMAIN'],
            $_ENV['API_HASH'],
            $_ENV['API_CUSTOMER_SECRET']
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