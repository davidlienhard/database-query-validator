<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\DumpData;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigTestCase extends TestCase
{
    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Config
     * @test
     */
    public function testCanBeCreated(): void
    {
        $config = new Config([]);

        $this->assertInstanceOf(Config::class, $config);
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Config
     * @test
     */
    public function testCannotBeCreatedWithoutData(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new Config;
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Config
     * @test
     */
    public function testCanGetData(): void
    {
        $config = new Config([
            "file" => "testfile",
            "list" => [
                "item1",
                "item2",
                "item3",
                "item4",
                "item5"
            ],
            "bool" => true,
            "null" => null
        ]);

        $this->assertEquals("testfile", $config->get("file"));

        $this->assertEquals("item1", $config->get("list")[0]);
        $this->assertEquals("item2", $config->get("list")[1]);
        $this->assertEquals(
            [
                "item1",
                "item2",
                "item3",
                "item4",
                "item5"
            ],
            $config->get("list")
        );

        $this->assertEquals(true, $config->get("bool"));
        $this->assertEquals(null, $config->get("null"));
    }


    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Config
     * @test
     */
    public function testInexistentKeyReturnsNull(): void
    {
        $config = new Config([]);

        $this->assertEquals(null, $config->get("file"));
        $this->assertEquals(null, $config->get("parameters", "file"));
    }
}
