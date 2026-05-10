<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Tester\Tests;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Queries\Query;
use DavidLienhard\Database\QueryValidator\Tester\Tests\TestAbstract;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

class TestAbstractTest extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\TestAbstract
     * @test
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testGetErrorCount(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;

        $params = [ $query, $dump ];

        $stub = $this->getMockBuilder(TestAbstract::class)
            ->setConstructorArgs($params)
            ->getMock();
        $stub->method('validate')->willReturn(true);

        $this->assertEquals(0, $stub->getErrorCount());
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Tester\Tests\TestAbstract
     * @test
     */
    #[AllowMockObjectsWithoutExpectations]
    public function testGetErrors(): void
    {
        $query = new Query("SELECT * FROM `table`", [], "testfile.php", 1);
        $dump = new DumpData;

        $params = [ $query, $dump ];

        $stub = $this->getMockBuilder(TestAbstract::class)
            ->setConstructorArgs($params)
            ->getMock();
        $stub->method('validate')->willReturn(true);

        $this->assertEquals([], $stub->getErrors());
    }
}
