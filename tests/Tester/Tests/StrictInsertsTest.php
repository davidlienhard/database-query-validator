<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\DumpData\Column;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\Query;
use DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts as StrictInsertsTest;
use DavidLienhard\Database\QueryValidator\Tester\Tests\TestInterface;
use PHPUnit\Framework\TestCase;

class StrictInsertsTestCase extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testCanBeCreated(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertInstanceOf(StrictInsertsTest::class, $inserts);
        $this->assertInstanceOf(TestInterface::class, $inserts);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testNonInsertQueriesReturnTrue(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertTrue($inserts->validate());
        $this->assertEquals(0, $inserts->getErrorcount());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testInsertWithoutTablenameReturnsFalse(): void
    {
        $query = new Query("INSERT INTO SET `userName` = ''", [], "testfile.php", 1);
        $dump = new DumpData;
        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertFalse($inserts->validate());
        $this->assertEquals(1, $inserts->getErrorcount());

        $this->assertEquals(
            [ "tablename could not be found in query" ],
            $inserts->getErrors()
        );
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testCanIgnoreInsertWithoutTablename(): void
    {
        $query = new Query("INSERT INTO SET `userName` = ''", [], "testfile.php", 1);
        $dump = new DumpData;
        $inserts = new StrictInsertsTest($query, $dump, [ "strictinsertsignoremissingtablenames" => true ]);

        $this->assertTrue($inserts->validate());
        $this->assertEquals(0, $inserts->getErrorcount());
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testReturnFalseForTableNotInDump(): void
    {
        $query = new Query("INSERT INTO `user` SET `userName` = ''", [], "testfile.php", 1);
        $dump = new DumpData;
        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertFalse($inserts->validate());
        $this->assertEquals(1, $inserts->getErrorcount());

        $this->assertEquals(
            [ "no colums for table 'user' found in dump" ],
            $inserts->getErrors()
        );
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testReturnFalseForQueryWithoutColums(): void
    {
        $query = new Query("INSERT INTO `user`", [], "testfile.php", 1);
        $dump = new DumpData(
            [
                new Column("user", "userName", "s", false, false)
            ]
        );
        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertFalse($inserts->validate());
        $this->assertEquals(1, $inserts->getErrorcount());

        $this->assertEquals(
            [ "could not find colums in current query" ],
            $inserts->getErrors()
        );
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testReturnTrueForValidQuery(): void
    {
        $query = new Query(
            "INSERT INTO
                `user`
            SET
                `userName` = ?",
            [
                "new DBParam(\"s\", \"test\")"
            ],
            "testfile.php",
            1
        );

        $dump = new DumpData(
            [
                new Column("user", "userName", "s", false, false)
            ]
        );

        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertTrue($inserts->validate());
        $this->assertEquals(0, $inserts->getErrorcount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts
     * @test
     */
    public function testReturnFalseForInvalidQuery(): void
    {
        $query = new Query(
            "INSERT INTO
                `user`
            SET
                `userName` = ?",
            [
                "new DBParam(\"s\", \"test\")"
            ],
            "testfile.php",
            1
        );

        $dump = new DumpData(
            [
                new Column("user", "userName", "s", false, false),
                new Column("user", "userDescription1", "s", false, true),
                new Column("user", "userDescription2", "s", true, true)
            ]
        );

        $inserts = new StrictInsertsTest($query, $dump);

        $this->assertFalse($inserts->validate());
        $this->assertEquals(1, $inserts->getErrorcount());

        $this->assertEquals(
            [ "column 'userDescription1' is missing in insert" ],
            $inserts->getErrors()
        );
    }
}
