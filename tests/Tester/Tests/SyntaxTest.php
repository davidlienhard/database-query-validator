<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Queries;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\Query;
use DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax as SyntaxTest;
use DavidLienhard\Database\QueryValidator\Tester\Tests\TestInterface;
use PHPUnit\Framework\TestCase;

class SyntaxTestCase extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax
     * @test
     */
    public function testCanBeCreated(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;
        $syntax = new SyntaxTest($query, $dump);

        $this->assertInstanceOf(SyntaxTest::class, $syntax);
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
        $syntax = new SyntaxTest($query, $dump);

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
        $syntax = new SyntaxTest($query, $dump);

        $this->assertFalse($syntax->validate());
        $this->assertEquals(1, $syntax->getErrorcount());
    }
}
