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
}
