<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\DumpData;

use DavidLienhard\Database\QueryValidator\DumpData\Column;
use DavidLienhard\Database\QueryValidator\DumpData\ColumnInterface;
use PHPUnit\Framework\TestCase;

class SyntaxTestCase extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\Column
     * @test
     */
    public function testCanBeCreated(): void
    {
        $column = new Column("user", "userName", "s", false, false);

        $this->assertInstanceOf(Column::class, $column);
        $this->assertInstanceOf(ColumnInterface::class, $column);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\Column
     * @test
     */
    public function testCanGetTableName(): void
    {
        $tableName = random_bytes(50);
        $column = new Column($tableName, "userName", "s", false, false);

        $this->assertEquals($tableName, $column->getTable());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\Column
     * @test
     */
    public function testCanGetColumnName(): void
    {
        $columnName = random_bytes(50);
        $column = new Column("user", $columnName, "s", false, false);

        $this->assertEquals($columnName, $column->getName());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\Column
     * @test
     */
    public function testCanGetColumnType(): void
    {
        $types = [ "s", "i", "d" ];

        foreach ($types as $type) {
            $column = new Column("user", "userName", $type, false, false);
            $this->assertEquals($type, $column->getType());
        }
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\Column
     * @test
     */
    public function testCanGetColumnIsNull(): void
    {
        $column = new Column("user", "userName", "s", false, false);
        $this->assertEquals(false, $column->isNull());

        $column = new Column("user", "userName", "s", true, false);
        $this->assertEquals(true, $column->isNull());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\DumpData\Column
     * @test
     */
    public function testCanGetColumnIsText(): void
    {
        $column = new Column("user", "userName", "s", false, false);
        $this->assertEquals(false, $column->isText());

        $column = new Column("user", "userName", "s", false, true);
        $this->assertEquals(true, $column->isText());
    }
}
