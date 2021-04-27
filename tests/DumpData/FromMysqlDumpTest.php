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

            $this->assertEquals(
                [
                    "user",
                    "userID",
                    "i",
                    false,
                    false
                ],
                [
                    $dump->getWithTable("user", "userID")->getTable(),
                    $dump->getWithTable("user", "userID")->getName(),
                    $dump->getWithTable("user", "userID")->getType(),
                    $dump->getWithTable("user", "userID")->isNull(),
                    $dump->getWithTable("user", "userID")->isText()
                ]
            );

            $this->assertEquals(
                [
                    "user",
                    "userName",
                    "s",
                    false,
                    false
                ],
                [
                    $dump->getWithTable("user", "userName")->getTable(),
                    $dump->getWithTable("user", "userName")->getName(),
                    $dump->getWithTable("user", "userName")->getType(),
                    $dump->getWithTable("user", "userName")->isNull(),
                    $dump->getWithTable("user", "userName")->isText()
                ]
            );

            $this->assertEquals(
                [
                    "user",
                    "userDescription",
                    "s",
                    false,
                    true
                ],
                [
                    $dump->getWithTable("user", "userDescription")->getTable(),
                    $dump->getWithTable("user", "userDescription")->getName(),
                    $dump->getWithTable("user", "userDescription")->getType(),
                    $dump->getWithTable("user", "userDescription")->isNull(),
                    $dump->getWithTable("user", "userDescription")->isText()
                ]
            );

            $this->assertEquals(
                [
                    "user",
                    "userPermissions",
                    "i",
                    false,
                    false
                ],
                [
                    $dump->getWithTable("user", "userPermissions")->getTable(),
                    $dump->getWithTable("user", "userPermissions")->getName(),
                    $dump->getWithTable("user", "userPermissions")->getType(),
                    $dump->getWithTable("user", "userPermissions")->isNull(),
                    $dump->getWithTable("user", "userPermissions")->isText()
                ]
            );
        }//end if
    }
}
