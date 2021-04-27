<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump;
use PHPUnit\Framework\TestCase;

class FromMysqlDumpTest extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump::getDumpData()
     * @test
     */
    public function testThrowsWithoutFile(): void
    {
        $this->expectException(\ArgumentCountError::class);
        $dump = FromMysqlDump::getDumpData();
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump::getDumpData()
     * @test
     */
    public function testThrowsWithMissingFile(): void
    {
        $dumpFile = "doesnotexist.sql";

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("dumpfile '".$dumpFile."' does not exist");
        $dump = FromMysqlDump::getDumpData($dumpFile);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump::getDumpData()
     * @test
     */
    /* public function testThrowsWhenReadingFolder(): void
    {
        $dumpFile = __DIR__;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("dumpfile '".$dumpFile."' does not exist");
        $dump = FromMysqlDump::getDumpData($dumpFile);
    } */


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump::getDumpData()
     * @covers DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump::convertType()
     * @test
     */
    public function testCanReadFile(): void
    {
        $files = [ "user.sql", "userUppercaseTypes.sql" ];

        foreach ($files as $file) {
            $dumpFile = dirname(__DIR__)."/assets/DumpData/".$file;

            $dump = FromMysqlDump::getDumpData($dumpFile);

            $this->assertInstanceOf(DumpData::class, $dump);

            $this->assertEquals("i", $dump->getWithTable("user", "userID"));
            $this->assertEquals("s", $dump->getWithTable("user", "userName"));
            $this->assertEquals("s", $dump->getWithTable("user", "userDescription"));
            $this->assertEquals("i", $dump->getWithTable("user", "userPermissions"));

            $this->assertEquals("i", $dump->getWithoutTable("userID"));
            $this->assertEquals("s", $dump->getWithoutTable("userName"));
            $this->assertEquals("s", $dump->getWithoutTable("userDescription"));
            $this->assertEquals("i", $dump->getWithoutTable("userPermissions"));
        }
    }
}
