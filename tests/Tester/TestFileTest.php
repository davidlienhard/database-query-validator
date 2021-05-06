<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\DumpData\Column;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Output\Standard as StandardOutput;
use DavidLienhard\Database\QueryValidator\Tester\TestFile;
use DavidLienhard\Database\QueryValidator\Tester\TestFileInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class TestFileTestCase extends TestCase
{
    protected static array $queries = [];

    private function getFilesystem() : Filesystem
    {
        $adapter = new InMemoryFilesystemAdapter;
        return new Filesystem($adapter);
    }

    public static function setUpBeforeClass() : void
    {
        self::$queries['invalidFile'] = <<<CODE
        <?php
        declare(strict_types=1);

        has parse error
        CODE;

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

        self::$queries['singleValidInsertQuery'] = <<<CODE
        <?php
        declare(strict_types=1);

        use DavidLienhard\Database\Parameter as DBParam;

        \$db->query(
            "INSERT INTO
                `user`
            SET
                `userName` = ?,
                `userMail` = ?",
            new DBParam("s", \$userName),
            new DBParam("s", \$userMail)
        );
        CODE;
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanBeCreated(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("empty.php", "");

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile("empty.php", $filesystem, $config, $output, $dumpData);

        $this->assertInstanceOf(TestFile::class, $testFile);
        $this->assertInstanceOf(TestFileInterface::class, $testFile);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCannotBeCreatedWithInexistentFile(): void
    {
        $filesystem = $this->getFilesystem();

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $this->expectException(\Exception::class);
        new TestFile("doesnotexist", $filesystem, $config, $output, $dumpData);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanValidateEmptyFileWithoutErrors(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("empty.php", "");

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        ob_start();
        $testFile = new TestFile("empty.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals(0, $testFile->getQueryCount());
        $this->assertEquals([], $testFile->getErrors());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testGetParseErrorOnInvalidFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("invalidFile.php", self::$queries['invalidFile']);


        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile("invalidFile.php", $filesystem, $config, $output, $dumpData);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/^Parse error: (.*) \(invalidFile.php\)$/");
        $testFile->validate();
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanValidateFileWithSingleQueryWithoutErrors(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidQuery.php", self::$queries['singleValidQuery']);


        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        ob_start();
        $testFile = new TestFile("singleValidQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
        $this->assertEquals([], $testFile->getErrors());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testDoesValidateSyntax(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleInvalidQuery.php", self::$queries['singleInvalidQuery']);

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData;

        ob_start();
        $testFile = new TestFile("singleInvalidQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertGreaterThan(0, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanIgnoreSyntaxValidation(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleInvalidQuery.php", self::$queries['singleInvalidQuery']);

        $config = new Config(
            [ "parameters" => [ "ignoresyntax" => true ]]
        );
        $output = new StandardOutput;
        $dumpData = new DumpData;

        ob_start();
        $testFile = new TestFile("singleInvalidQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals([], $testFile->getErrors());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testDoesValidateValidDumpData(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidQuery.php", self::$queries['singleValidQuery']);

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [ new Column("user", "userLevel", "i", false, false) ]
        );

        ob_start();
        $testFile = new TestFile("singleValidQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals([], $testFile->getErrors());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testDoesValidateInvalidDumpData(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidQuery.php", self::$queries['singleValidQuery']);

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [ new Column("user", "userLevel", "s", false, false) ]
        );

        ob_start();
        $testFile = new TestFile("singleValidQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(1, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testDoesNotValidateMissingTextColumnOnStrictInsert(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidInsertQuery.php", self::$queries['singleValidInsertQuery']);

        $config = new Config([]);
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false),
                new Column("user", "userDescription", "s", false, true)
            ]
        );

        ob_start();
        $testFile = new TestFile("singleValidInsertQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals([], $testFile->getErrors());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanEnableStrictInsert(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidInsertQuery.php", self::$queries['singleValidInsertQuery']);

        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]]
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false),
                new Column("user", "userDescription", "s", false, true)
            ]
        );

        ob_start();
        $testFile = new TestFile("singleValidInsertQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(1, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testStrictInsertDoesNotReportNullableTextColums(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidInsertQuery.php", self::$queries['singleValidInsertQuery']);

        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]]
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false),
                new Column("user", "userDescription", "s", true, true)
            ]
        );

        ob_start();
        $testFile = new TestFile("singleValidInsertQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals([], $testFile->getErrors());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testDoesValidateValidDumpDataOnStrictInsert(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidInsertQuery.php", self::$queries['singleValidInsertQuery']);

        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]]
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false)
            ]
        );

        ob_start();
        $testFile = new TestFile("singleValidInsertQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals([], $testFile->getErrors());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testChecksMissingTableInDump(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidInsertQuery.php", self::$queries['singleValidInsertQuery']);

        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]]
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("userTable", "userName", "s", false, false),
                new Column("userTable", "userMail", "s", false, false)
            ]
        );

        ob_start();
        $testFile = new TestFile("singleValidInsertQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(1, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanIgnoreMissingTableInDump(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("singleValidInsertQuery.php", self::$queries['singleValidInsertQuery']);

        $config = new Config(
            [
                "parameters" => [
                    "strictinserts"                        => true,
                    "strictinsertsignoremissingtablenames" => true
                ]
            ]
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("userTable", "userName", "s", false, false),
                new Column("userTable", "userMail", "s", false, false)
            ]
        );

        ob_start();
        $testFile = new TestFile("singleValidInsertQuery.php", $filesystem, $config, $output, $dumpData);
        $testFile->validate();
        ob_end_clean();

        $this->assertEquals(1, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }
}
