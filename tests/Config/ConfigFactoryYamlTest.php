<?php declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tests\DumpData;

use DavidLienhard\Database\QueryValidator\Config\Config;
use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\Config\Factory as ConfigFactory;
use DavidLienhard\Database\QueryValidator\Exceptions\Config as ConfigException;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class ConfigFactoryYamlTestCase extends TestCase
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
    public function testCanCreateConfigFromEmptyYamlFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.yml", "");

        $config = ConfigFactory::fromYaml($filesystem, "config.yml");

        $this->assertInstanceOf(Config::class, $config);
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testGetExceptionFromInexistentYamlFile(): void
    {
        $filesystem = $this->getFilesystem();

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("configuration file 'config.yml' does not exist");
        ConfigFactory::fromYaml($filesystem, "config.yml");
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testGetExceptionFromInvalidYamlFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.yml", "{");

        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage("unable to decode yaml data");
        ConfigFactory::fromYaml($filesystem, "config.yml");
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanReadDataFromYamlFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.yml", "key: value");

        $config = ConfigFactory::fromYaml($filesystem, "config.yml");
        $this->assertEquals("value", $config->get("key"));
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanReadListFromYamlFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.yml", "list:\n- value 1\n- value 2");

        $result = [
            "value 1",
            "value 2"
        ];

        $config = ConfigFactory::fromYaml($filesystem, "config.yml");
        $this->assertEquals($result, $config->get("list"));
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanReadArrayFromYamlFile(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.yml", "array:\n  key1: value 1\n  key2: value 2");

        $result = [
            "key1" => "value 1",
            "key2" => "value 2"
        ];

        $config = ConfigFactory::fromYaml($filesystem, "config.yml");
        $this->assertEquals($result, $config->get("array"));
    }

    /**
     * @covers DavidLienhard\Database\QueryValidator\Config\Factory
     * @test
     */
    public function testCanAddFromstdinKeyFromArgument(): void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("config.yml", "");


        $_SERVER['argv'] = [];
        $config = ConfigFactory::fromYaml($filesystem, "config.yml");
        $this->assertEquals(null, $config->get("parameters", "fromstdin"));

        $_SERVER['argv'][] = "--from-stdin";
        $config = ConfigFactory::fromYaml($filesystem, "config.yml");
        $this->assertEquals(true, $config->get("parameters", "fromstdin"));
    }
}
