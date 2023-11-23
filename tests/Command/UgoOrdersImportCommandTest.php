<?php

namespace App\Tests\Command;


use App\Command\UgoOrdersImportCommand;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class UgoOrdersImportCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    /**
     * @throws Exception
     * @throws Exception
     */
    protected function setUp(): void
    {
        $entityManager  = $this->createMock(EntityManagerInterface::class);
        $connection     = $this->createMock(Connection::class);
        $schemaManager  = $this->createMock(AbstractSchemaManager::class);

        $entityManager->method('getConnection')->willReturn($connection);
        $connection->method('createSchemaManager')->willReturn($schemaManager);

        $command = new UgoOrdersImportCommand($entityManager);

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->add($command);
        $this->commandTester = new CommandTester($application->find('ugo:orders:import'));
    }

    /*public function testExecute(): void
    {
        // Simulate command execution with inputs
        $this->commandTester->setInputs(['./tests/data/successfulImport/customers.csv', './tests/data/successfulImport/purchases.csv']);
        $this->commandTester->execute([]);

        // Assert the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('CSV files have been imported successfully.', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }*/

    public function testExecuteWithInvalidPath(): void
    {
        $this->commandTester->setInputs(['./.gitignore', './.gitignore']);
        $this->commandTester->execute([]);

        $this->assertStringContainsString('One or both of the files are invalid', $this->commandTester->getDisplay());
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }

    /*public function testExecuteWithInvalidWrongColumnsCsvFile(): void
    {
        $this->commandTester->setInputs(['./tests/data/wrongColumns/customers.csv', './tests/data/wrongColumns/purchases.csv']);
        $this->commandTester->execute([]);

        $this->assertStringContainsString('CSV import into database failed', $this->commandTester->getDisplay());
        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
    }*/
}
