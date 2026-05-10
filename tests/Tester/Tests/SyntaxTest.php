<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\Query;
use DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax;
use DavidLienhard\Database\QueryValidator\Tester\Tests\TestInterface;
use PHPUnit\Framework\TestCase;

class SyntaxTest extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax
     * @test
     */
    public function testCanBeCreated(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $syntax = new Syntax($query, $dump);

        $this->assertInstanceOf(Syntax::class, $syntax);
        $this->assertInstanceOf(TestInterface::class, $syntax);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax
     * @test
     */
    public function testReturnTrueForValidQuery(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $syntax = new Syntax($query, $dump);

        $this->assertTrue($syntax->validate());
        $this->assertEquals(0, $syntax->getErrorcount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax
     * @test
     */
    public function testReturnFalseForInvalidQuery(): void
    {
        $query = new Query("invalidquery", [], "testfile.php", 1);
        $dump = new DumpData;
        $syntax = new Syntax($query, $dump);

        $this->assertFalse($syntax->validate());
        $this->assertEquals(1, $syntax->getErrorcount());
    }
}
