<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\TabularDataReader;
use League\Csv\UnavailableStream;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use League\Csv\Reader;
use League\Csv\Statement;
use function PHPUnit\Framework\throwException;

#[AsCommand(
    name: 'ugo:orders:import',
    description: 'Imports csv files',
)]
class UgoOrdersImportCommand extends Command
{

    private EntityManagerInterface $entityManager;

    private AbstractSchemaManager $schemaManager;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->schemaManager = $entityManager->getConnection()->createSchemaManager();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('inputCsvCustomers',
                InputArgument::OPTIONAL,
                'Path to the customers.csv (default ./customers.csv)')
            ->addArgument('inputCsvPurchases',
                InputArgument::OPTIONAL,
                'Path to the purchases.csv (default ./purchases.csv)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $inputCsvCustomers = $input->getArgument('inputCsvCustomers') ?? './var/data/customers.csv';
        $inputCsvPurchases = $input->getArgument('inputCsvPurchases') ?? './var/data/purchases.csv';

        if ($inputCsvCustomers === './var/data/customers.csv') {
            $inputCsvCustomers = $io->ask('Enter the path for the customers CSV file', $inputCsvCustomers);
        }
        if ($inputCsvPurchases === './var/data/purchases.csv') {
            $inputCsvPurchases = $io->ask('Enter the path for the purchases CSV file', $inputCsvPurchases);
        }


        $validation = $this->validateCsvFile($inputCsvCustomers) && $this->validateCsvFile($inputCsvPurchases);
        if ($validation) {
            try {
                $this->importCustomers(
                    $io,
                    $this->readCsv(
                        $io,
                        $inputCsvCustomers
                    )
                );
                $this->importPurchases(
                    $io,
                    $this->readCsv(
                        $io,
                        $inputCsvPurchases
                    )
                );

                $io->success('CSV files have been imported successfully.');
                return Command::SUCCESS;
            } catch (\Exception $exception) {
                $io->error($exception->getMessage());
                $io->error('CSV import into database failed');
                return Command::FAILURE;
            }
        } else {
            $io->error('One or both of the files are invalid');
            return Command::FAILURE;
        }
    }

    /**
     * @throws UnavailableStream
     * @throws SyntaxError
     * @throws Exception
     */
    private function readCsv(SymfonyStyle $io, string $csvPath): TabularDataReader
    {
        $fileContent = file_get_contents($csvPath);
        if (str_contains($fileContent, ',')) {
            $io->warning('Csv file contains ",", proceeding by replacing them with ";" to force data handling');
            $fileContent = str_replace(',', ';', $fileContent);
        }

        $csv = Reader::createFromString($fileContent, 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        return Statement::create()->process($csv);
    }

    private function validateCsvFile(string $csvPath): bool
    {
        return file_exists($csvPath) && pathinfo($csvPath, PATHINFO_EXTENSION) === 'csv';
    }


    /**
     * @throws \Exception
     */
    private function importCustomers(SymfonyStyle $io, TabularDataReader $records): void
    {
        $columns = $this->schemaManager->listTableColumns('customer');
        $dbColumnCount  = count($columns);
        $csvColumnCount = count($records->getHeader());

        if ($dbColumnCount !== $csvColumnCount) {
            throw new \Exception('Invalid Customers file');
        }

        $this->entityManager->beginTransaction();
        try {
            foreach ($records as $record) {
                $customer = new Customer();
                $customer->setId((int)$record['customer_id']);
                $title = match ((int)$record['title']) {
                    1 => 'mme',
                    2 => 'm',
                };
                $customer->setTitle($title);
                $customer->setLastname($record['lastname']);
                $customer->setFirstname($record['firstname']);
                $customer->setPostalCode((int)$record['postal_code']);
                $customer->setCity($record['city']);
                if (!$record['email']) {
                    $record['email'] = 'invalid';
                    $io->warning('Invalid email within customer table with id=' . $record['customer_id'] . ' please correct this as soon as possible and make sure to handle orders linked to this customer id appropriately');
                }
                $customer->setEmail($record['email']);

                $this->entityManager->persist($customer);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            $io->success('successfully imported customers into database');

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @throws \Exception
     */
    private function importPurchases(SymfonyStyle $io, TabularDataReader $records): void
    {
        $columns = $this->schemaManager->listTableColumns('order');
        $dbColumnCount  = count($columns);
        $csvColumnCount = count($records->getHeader());

        if ($dbColumnCount !== $csvColumnCount) {
            throw new \Exception('Invalid Purchases file');
        }
        $this->entityManager->beginTransaction();
        try {
            foreach ($records as $record) {
                //$io->info(json_encode($record));
                $order = new Order();

                $customer = $this->entityManager->getRepository(Customer::class)->find($record['customer_id']);
                if (!$customer) {
                    throw new \Exception("Customer ID " . $record['customer_id'] . " unknown");
                }
                $order->setCustomer($customer);
                $order->setProductId($record['product_id']);
                $order->setQuantity($record['quantity']);
                $order->setPrice($record['price']);
                $order->setCurrency($record['currency']);
                $order->setDate(new \DateTime($record['date']));

                $this->entityManager->persist($order);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            $io->success('successfully imported purchases into database');

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $io->error($e->getMessage());
            throw $e;
        }

    }
}
