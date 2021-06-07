<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\Column;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\Query;
use DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters as ParametersTest;
use DavidLienhard\Database\QueryValidator\Tester\Tests\TestInterface;
use PHPUnit\Framework\TestCase;

class ParametersTestTest extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testCanBeCreated(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $parameters = new ParametersTest($query, $dump);

        $this->assertInstanceOf(ParametersTest::class, $parameters);
        $this->assertInstanceOf(TestInterface::class, $parameters);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnTrueForQueryWithoutParameters(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $parameters = new ParametersTest($query, $dump);

        $this->assertTrue($parameters->validate());
        $this->assertEquals(0, $parameters->getErrorcount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnTrueForQueryWithValidParameters(): void
    {
        $query = new Query(
            "SELECT * FROM `table` WHERE `userID` = ?",
            [
                "new DBParam(\"i\", 1)"
            ],
            "testfile.php",
            1
        );
        $dump = new DumpData;
        $parameters = new ParametersTest($query, $dump);

        $this->assertTrue($parameters->validate());
        $this->assertEquals(0, $parameters->getErrorcount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnFalseForQueryWithInvalidParametercount(): void
    {
        $query = new Query(
            "SELECT * FROM `table` WHERE `userID` = ? and `userName` = ?",
            [
                "new DBParam(\"i\", 1)"
            ],
            "testfile.php",
            1
        );
        $dump = new DumpData;
        $parameters = new ParametersTest($query, $dump);

        $this->assertFalse($parameters->validate());
        $this->assertEquals(1, $parameters->getErrorcount());
        $this->assertStringContainsString(
            "number of question marks in query (2) or number of data parameters (1)",
            $parameters->getErrors()[0]
        );
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnFalseForQueryWithInvalidParameter(): void
    {
        $query = new Query(
            "SELECT * FROM `table` WHERE `userID` = ?",
            [
                "invalid param"
            ],
            "testfile.php",
            1
        );
        $dump = new DumpData;
        $parameters = new ParametersTest($query, $dump);
        $this->assertFalse($parameters->validate());

        $this->assertEquals(2, $parameters->getErrorcount());
        $this->assertStringContainsString(
            "parameter '1' is invalid",
            $parameters->getErrors()[0]
        );
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnTrueForQueryWithValidParameterAndDumpWithTable(): void
    {
        $query = new Query(
            "SELECT
                `userName`
            FROM
                `user`
            WHERE
                `user`.`userID` = ?",
            [
                "new DBParam(\"i\", 1)"
            ],
            "testfile.php",
            1
        );

        $dump = new DumpData(
            [
                new Column("user", "userID", "i", false, false)
            ]
        );

        $parameters = new ParametersTest($query, $dump);
        $this->assertTrue($parameters->validate());
        $this->assertEquals(0, $parameters->getErrorcount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnFalseForQueryWithInvalidParameterAndDumpWithTable(): void
    {
        $query = new Query(
            "SELECT
                `userName`
            FROM
                `user`
            WHERE
                `user`.`userID` = ?",
            [
                "new DBParam(\"s\", 1)"
            ],
            "testfile.php",
            1
        );

        $dump = new DumpData(
            [
                new Column("user", "userID", "i", false, false)
            ]
        );

        $parameters = new ParametersTest($query, $dump);
        $this->assertFalse($parameters->validate());
        $this->assertEquals(1, $parameters->getErrorcount());
        $this->assertStringContainsString(
            "given type 's' ".
            "does not match dump type 'i' ".
            "in column `user`.`userID`",
            $parameters->getErrors()[0]
        );
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnTrueForQueryWithValidParameterAndDumpWithoutTable(): void
    {
        $query = new Query(
            "SELECT
                `userName`
            FROM
                `user`
            WHERE
                `userID` = ?",
            [
                "new DBParam(\"i\", 1)"
            ],
            "testfile.php",
            1
        );

        $dump = new DumpData(
            [
                new Column("user", "userID", "i", false, false)
            ]
        );

        $parameters = new ParametersTest($query, $dump);
        $this->assertTrue($parameters->validate());
        $this->assertEquals(0, $parameters->getErrorcount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters
     * @test
     */
    public function testReturnFalseForQueryWithInvalidParameterAndDumpWithoutTable(): void
    {
        $query = new Query(
            "SELECT
                `userName`
            FROM
                `user`
            WHERE
                `userID` = ?",
            [
                "new DBParam(\"s\", 1)"
            ],
            "testfile.php",
            1
        );

        $dump = new DumpData(
            [
                new Column("user", "userID", "i", false, false)
            ]
        );

        $parameters = new ParametersTest($query, $dump);
        $this->assertFalse($parameters->validate());
        $this->assertEquals(1, $parameters->getErrorcount());
        $this->assertStringContainsString(
            "given type 's' ".
            "does not match dump type 'i' ".
            "in column `userID`",
            $parameters->getErrors()[0]
        );
    }
}
