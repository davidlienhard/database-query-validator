<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\DumpData\Column;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Output\Standard as StandardOutput;
use DavidLienhard\Database\QueryValidator\Tester\TestFile;
use DavidLienhard\Database\QueryValidator\Tester\TestFileInterface;
use PHPUnit\Framework\TestCase;

class TestFileTestCase extends TestCase
{
    private function getDummyConfig() : ConfigInterface
    {
        return new Config([], dirname(__DIR__, 2)."/assets/Tester/TestFile/dummyconfig.json");
    }

    private function getEmptyPhpFile() : string
    {
        return dirname(__DIR__, 2)."/assets/Tester/TestFile/empty.php";
    }

    private function getAssetsFolder() : string
    {
        return dirname(__DIR__, 2)."/assets/Tester/TestFile/";
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanBeCreated(): void
    {
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile($this->getEmptyPhpFile(), $config, $output, $dumpData);

        $this->assertInstanceOf(TestFile::class, $testFile);
        $this->assertInstanceOf(TestFileInterface::class, $testFile);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCannotBeCreatedWithInexistentFile(): void
    {
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $this->expectException(\Exception::class);
        new TestFile("doesnotexist", $config, $output, $dumpData);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanValidateEmptyFileWithoutErrors(): void
    {
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile($this->getEmptyPhpFile(), $config, $output, $dumpData);
        $testFile->validate();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals(0, $testFile->getQueryCount());
        $this->assertEquals([], $testFile->getErrors());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanValidateFileWithSingleQueryWithoutErrors(): void
    {
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile($this->getAssetsFolder()."singleValidQuery.php", $config, $output, $dumpData);
        $testFile->validate();

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
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile($this->getAssetsFolder()."singleInvalidQuery.php", $config, $output, $dumpData);
        $testFile->validate();

        $this->assertGreaterThan(0, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testCanIgnoreSyntaxValidation(): void
    {
        $config = new Config(
            [ "parameters" => [ "ignoresyntax" => true ]],
            $this->getAssetsFolder()."dummyconfig.json"
        );
        $output = new StandardOutput;
        $dumpData = new DumpData;

        $testFile = new TestFile($this->getAssetsFolder()."singleInvalidQuery.php", $config, $output, $dumpData);
        $testFile->validate();

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
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [ new Column("user", "userLevel", "i", false, false) ]
        );

        $testFile = new TestFile($this->getAssetsFolder()."singleValidQuery.php", $config, $output, $dumpData);
        $testFile->validate();

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
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [ new Column("user", "userLevel", "s", false, false) ]
        );

        $testFile = new TestFile($this->getAssetsFolder()."singleValidQuery.php", $config, $output, $dumpData);
        $testFile->validate();

        $this->assertEquals(1, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testDoesNotValidateMissingTextColumnOnStrictInsert(): void
    {
        $config = $this->getDummyConfig();
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false),
                new Column("user", "userDescription", "s", false, true)
            ]
        );

        $testFile = new TestFile($this->getAssetsFolder()."singleValidInsertQuery.php", $config, $output, $dumpData);
        $testFile->validate();

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
        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]],
            $this->getAssetsFolder()."dummyconfig.json"
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false),
                new Column("user", "userDescription", "s", false, true)
            ]
        );

        $testFile = new TestFile($this->getAssetsFolder()."singleValidInsertQuery.php", $config, $output, $dumpData);
        $testFile->validate();

        $this->assertEquals(1, $testFile->getErrorCount());
        $this->assertEquals(1, $testFile->getQueryCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\TestFile
     * @test
     */
    public function testStrictInsertDoesNotReportNullableTextColums(): void
    {
        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]],
            $this->getAssetsFolder()."dummyconfig.json"
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false),
                new Column("user", "userDescription", "s", true, true)
            ]
        );

        $testFile = new TestFile($this->getAssetsFolder()."singleValidInsertQuery.php", $config, $output, $dumpData);
        $testFile->validate();

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
        $config = new Config(
            [ "parameters" => [ "strictinserts" => true ]],
            $this->getAssetsFolder()."dummyconfig.json"
        );
        $output = new StandardOutput;
        $dumpData = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userMail", "s", false, false)
            ]
        );

        $testFile = new TestFile($this->getAssetsFolder()."singleValidInsertQuery.php", $config, $output, $dumpData);
        $testFile->validate();

        $this->assertEquals(0, $testFile->getErrorCount());
        $this->assertEquals([], $testFile->getErrors());
        $this->assertEquals(1, $testFile->getQueryCount());
    }
}
