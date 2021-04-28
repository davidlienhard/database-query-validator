<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\Column;
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
    public function testCannotInstantiateStringAsParam(): void
    {
        $this->expectException(\TypeError::class);
        $dump = new DumpData("string");
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCannotInstantiateColumnAsParam(): void
    {
        $this->expectException(\TypeError::class);
        $dump = new DumpData(new Column("user", "userID", "i", false, false));
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCannotInstantiateArrayOfStringsAsParam(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $dump = new DumpData([ "string" ]);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCannotInstantiateArrayOfIntsAsParam(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $dump = new DumpData([ 1 ]);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCannotInstantiateArrayOfBoolsAsParam(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $dump = new DumpData([ true ]);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCanFetchData(): void
    {
        $dump = new DumpData(
            [
                new Column("user", "userID", "i", false, false),
                new Column("user", "userName", "s", false, false),
                new Column("user", "userDescription", "s", false, true),
                new Column("user", "userPermissions", "i", false, false)
            ]
        );

        $this->assertEquals("i", $dump->getWithTable("user", "userID")->getType());
        $this->assertEquals("s", $dump->getWithTable("user", "userName")->getType());
        $this->assertEquals("s", $dump->getWithTable("user", "userDescription")->getType());
        $this->assertEquals("i", $dump->getWithTable("user", "userPermissions")->getType());

        $this->assertEquals("i", $dump->getWithoutTable("userID")->getType());
        $this->assertEquals("s", $dump->getWithoutTable("userName")->getType());
        $this->assertEquals("s", $dump->getWithoutTable("userDescription")->getType());
        $this->assertEquals("i", $dump->getWithoutTable("userPermissions")->getType());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\DumpData
     * @test
     */
    public function testCanGetAlColumsForTable(): void
    {
        $dumpData = [
            "userID"          => new Column("user", "userID", "i", false, false),
            "userName"        => new Column("user", "userName", "s", false, false),
            "userDescription" => new Column("user", "userDescription", "s", false, true),
            "userPermissions" => new Column("user", "userPermissions", "i", false, false)
        ];

        $dump = new DumpData(array_values($dumpData));

        $this->assertEquals($dumpData, $dump->getColumsForTable("user"));
        $this->assertNull($dump->getColumsForTable("doesnotexist"));
    }
}
