<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\DumpData;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\Config\Factory as ConfigFactory;
use DavidLienhard\Database\QueryValidator\Exceptions\Config as ConfigException;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class ConfigFactoryJsonTestCase extends TestCase
{
    private function getFilesystem() : Filesystem
    {
        $adapter = new InMemoryFilesystemAdapter;
        return new Filesystem($adapter);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanCreateConfigFromEmptyJsonFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.json", "{}");

        $config = ConfigFactory::fromJson($filesystem, "config.json");

        $this->assertInstanceOf(Config::class, $config);
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testGetExceptionFromInexistentJsonFile(): void
    {
        $filesystem = $this->getFilesystem();

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("configuration file 'config.json' does not exist");
        ConfigFactory::fromJson($filesystem, "config.json");
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testGetExceptionFromInvalidJsonFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.json", "{");

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("unable to decode json data");
        ConfigFactory::fromJson($filesystem, "config.json");
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanReadDataFromJsonFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.json", "{ \"key\": \"value\" }");

        $config = ConfigFactory::fromJson($filesystem, "config.json");
        $this->assertEquals("value", $config->get("key"));
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanReadListFromJsonFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.json", "{ \"list\": [ \"value 1\", \"value 2\" ]}");

        $result = [
            "value 1",
            "value 2"
        ];

        $config = ConfigFactory::fromJson($filesystem, "config.json");
        $this->assertEquals($result, $config->get("list"));
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanReadArrayFromJsonFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.json", "{ \"array\": { \"key1\": \"value 1\", \"key2\": \"value 2\" }}");

        $result = [
            "key1" => "value 1",
            "key2" => "value 2"
        ];

        $config = ConfigFactory::fromJson($filesystem, "config.json");
        $this->assertEquals($result, $config->get("array"));
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanAddFromstdinKeyFromArgument(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.json", "{}");


        $config = ConfigFactory::fromJson($filesystem, "config.json");
        $this->assertEquals(null, $config->get("parameters", "fromstdin"));

        $_SERVER['argv'][] = "--from-stdin";
        $config = ConfigFactory::fromJson($filesystem, "config.json");
        $this->assertEquals(true, $config->get("parameters", "fromstdin"));
    }
}
