<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use PHPUnit\Framework\TestCase;

class DumpDataTest extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCanBeCreated(): void
    {
        $dump = new DumpData;

        $this->assertInstanceOf(DumpData::class, $dump);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCanFetchData(): void
    {
        $dump = new DumpData(
            [
                [
                    "tableName"  => "user",
                    "columnName" => "userID",
                    "dataType"   => "i"
                ],
                [
                    "tableName"  => "user",
                    "columnName" => "userName",
                    "dataType"   => "s"
                ],
                [
                    "tableName"  => "user",
                    "columnName" => "userDescription",
                    "dataType"   => "s"
                ],
                [
                    "tableName"  => "user",
                    "columnName" => "userPermissions",
                    "dataType"   => "i"
                ]
            ]
        );

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
