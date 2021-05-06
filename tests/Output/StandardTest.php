<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\Output;

use DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use DavidLienhard\Database\QueryValidator\Output\Standard;
use DavidLienhard\Database\QueryValidator\Tester\Tester;
use PHPUnit\Framework\TestCase;

class StandardOutputTestCase extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testCanBeCreated(): void
    {
        $output = new Standard;

        $this->assertInstanceOf(Standard::class, $output);
        $this->assertInstanceOf(OutputInterface::class, $output);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testValidResultReturnsDot(): void
    {
        $output = new Standard;

        ob_start();
        $output->query("testfile.php", 1, true);
        $outputResult = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(".", $outputResult);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testInvalidResultReturnsX(): void
    {
        $output = new Standard;

        ob_start();
        $output->query("testfile.php", 1, false);
        $outputResult = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("x", $outputResult);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testNewlineAfter80Chars(): void
    {
        $output = new Standard;

        ob_start();

        for ($i = 0; $i < 100; $i++) {
            $output->query("testfile.php", 1, true);
        }

        $outputResult = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(str_repeat(".", 80).PHP_EOL.str_repeat(".", 20), $outputResult);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testOutputOfEmptySummary(): void
    {
        $output = new Standard;

        $tester = $this->createMock(Tester::class);
        $tester->method('getErrorcount')->willReturn(0);
        $tester->method('getFilecount')->willReturn(0);
        $tester->method('getQuerycount')->willReturn(0);
        $tester->method('getErrors')->willReturn([]);

        ob_start();
        $output->summary($tester);
        $outputResult = ob_get_contents();
        ob_end_clean();

        $summary = PHP_EOL."found 0 errors ".
            "in 0 files ".
            "and 0 queries".PHP_EOL.PHP_EOL;

        $this->assertEquals($summary, $outputResult);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testOutputOfSummaryWithValidQueries(): void
    {
        $output = new Standard;

        $tester = $this->createMock(Tester::class);
        $tester->method('getErrorcount')->willReturn(0);
        $tester->method('getFilecount')->willReturn(2);
        $tester->method('getQuerycount')->willReturn(5);
        $tester->method('getErrors')->willReturn([]);

        ob_start();
        $output->summary($tester);
        $outputResult = ob_get_contents();
        ob_end_clean();

        $summary = PHP_EOL."found 0 errors ".
            "in 2 files ".
            "and 5 queries".PHP_EOL.PHP_EOL;

        $this->assertEquals($summary, $outputResult);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Output\Standard
     * @test
     */
    public function testOutputOfSummaryWithInvalidQueries(): void
    {
        $output = new Standard;

        $tester = $this->createMock(Tester::class);
        $tester->method('getErrorcount')->willReturn(2);
        $tester->method('getFilecount')->willReturn(2);
        $tester->method('getQuerycount')->willReturn(5);
        $tester->method('getErrors')->willReturn([ "error 1", "error 2" ]);

        ob_start();
        $output->summary($tester);
        $outputResult = ob_get_contents();
        ob_end_clean();

        $summary = PHP_EOL."found 2 errors ".
            "in 2 files ".
            "and 5 queries".PHP_EOL.PHP_EOL.
            "- error 1".PHP_EOL.
            "- error 2".PHP_EOL;

        $this->assertEquals($summary, $outputResult);
    }
}
