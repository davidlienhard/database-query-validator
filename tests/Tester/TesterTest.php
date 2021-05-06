<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Exceptions\TestFile as TestFileException;
use DavidLienhard\Database\QueryValidator\Output\Standard as StandardOutput;
use DavidLienhard\Database\QueryValidator\Tester\Tester;
use DavidLienhard\Database\QueryValidator\Tester\TesterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class TesterTestCase extends TestCase
{
    protected static array $queries = [];

    private function getFilesystem() : Filesystem
    {
        $adapter = new InMemoryFilesystemAdapter;
        return new Filesystem($adapter);
    }

    public static function setUpBeforeClass() : void
    {
        self::$queries['singleValidQuery'] = <<<CODE
        <?php
        declare(strict_types=1);

        use DavidLienhard\Database\Parameter as DBParam;

        \$db->query(
        "SELECT
            `userID`,
            `userName`,
            `userMail`
        FROM
            `user`
        WHERE
            `userLevel` = ?",
        new DBParam("i", \$userLevel)
        );
        CODE;

        self::$queries['singleInvalidQuery'] = <<<CODE
        <?php
        declare(strict_types=1);

        \$db->query(
            "SELECT
                `userID`,
                `userName`,
                `userMai
            WHERE
                `userLevel` = ?"
        );
        CODE;
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tester
     * @test
     */
    public function testCanBeCreated(): void
    {
        $filesystem = $this->getFilesystem();

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $tester = new Tester($filesystem, $config, $output, $dumpData);

        $this->assertInstanceOf(Tester::class, $tester);
        $this->assertInstanceOf(TesterInterface::class, $tester);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tester
     * @test
     */
    public function testGetExceptionOnNotExistingFile(): void
    {
        $filesystem = $this->getFilesystem();

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $tester = new Tester($filesystem, $config, $output, $dumpData);

        $this->expectException(TestFileException::class);
        $tester->test("empty.php");
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tester
     * @test
     */
    public function testCanValidateEmptyFileWithoutErrors(): void
    {
        $filename = "empty.php";
        $filesystem = $this->getFilesystem();
        $filesystem->write($filename, "");

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $tester = new Tester($filesystem, $config, $output, $dumpData);
        $tester->test($filename);
        $this->assertEquals(0, $tester->getErrorcount());
        $this->assertEquals(1, $tester->getFilecount());
        $this->assertEquals(0, $tester->getQuerycount());
        $this->assertEquals([ $filename ], $tester->getScannedFiles());
        $this->assertEquals([], $tester->getErrors());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tester
     * @test
     */
    public function testCanValidateFileWithOneValidQueryWithoutErrors(): void
    {
        $filename = "singlevalidquery.php";
        $filesystem = $this->getFilesystem();
        $filesystem->write($filename, self::$queries['singleValidQuery']);

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $tester = new Tester($filesystem, $config, $output, $dumpData);

        ob_start();
        $tester->test($filename);
        ob_end_clean();

        $this->assertEquals(0, $tester->getErrorcount());
        $this->assertEquals(1, $tester->getFilecount());
        $this->assertEquals(1, $tester->getQuerycount());
        $this->assertEquals([ $filename ], $tester->getScannedFiles());
        $this->assertEquals([], $tester->getErrors());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tester
     * @test
     */
    public function testCanValidateFileWithOneInvalidQueryWithoutErrors(): void
    {
        $filename = "singleinvalidquery.php";
        $filesystem = $this->getFilesystem();
        $filesystem->write($filename, self::$queries['singleInvalidQuery']);

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $tester = new Tester($filesystem, $config, $output, $dumpData);

        ob_start();
        $tester->test($filename);
        ob_end_clean();

        $this->assertGreaterThan(1, $tester->getErrorcount());
        $this->assertEquals(1, $tester->getFilecount());
        $this->assertEquals(1, $tester->getQuerycount());
        $this->assertEquals([ $filename ], $tester->getScannedFiles());
        $this->assertGreaterThan(1, count($tester->getErrors()));
    }
}
