<?php declare(strict_types=1);

/**
 * main entrypoint to validate queries
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Database\QueryValidator;

use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\Config\Factory as ConfigFactory;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\DumpData\FromMysqlDump;
use DavidLienhard\Database\QueryValidator\Exceptions\Config as ConfigException;
use DavidLienhard\Database\QueryValidator\Exceptions\DumpData as DumpDataException;
use DavidLienhard\Database\QueryValidator\Output\Standard as StandardOutput;
use DavidLienhard\Database\QueryValidator\Scanner\FilesystemScanner;
use DavidLienhard\Database\QueryValidator\Scanner\StdinScanner;
use DavidLienhard\Database\QueryValidator\Tester\Tester;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * main entrypoint to validate queries
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
final class QueryValidator
{
    /**
     * main method to call
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public static function main() : void
    {
        try {
            (new static)->run();
        } catch (\Throwable $t) {
            throw new \RuntimeException(
                $t->getMessage(),
                intval($t->getCode()),
                $t
            );
        }
    }

    /**
     * runs the program
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function run() : void
    {
        $filesystem = $this->getFilesystem();
        $output = new StandardOutput;

        try {
            $config = $this->getConfig($filesystem);

            $paths = $this->getPaths($config);
            $exclusions = $this->getExclusions($config);
            $dumpData = $this->getDumpData($config);

            ini_set("xdebug.max_nesting_level", "1000");
        } catch (ConfigException $e) {
            $output->error(
                "error fetching configuration-data".PHP_EOL.
                "    ".$e->getMessage().PHP_EOL
            );
            exit(1);
        } catch (DumpDataException $e) {
            $output->error(
                "error fetching dump-data".PHP_EOL.
                "    ".$e->getMessage().PHP_EOL
            );
            exit(1);
        }//end try

        $tester = new Tester($filesystem, $config, $output, $dumpData);

        $fromStdin = boolval($config->get("parameters", "fromstdin") ?? false);

        $scanner = !$fromStdin
            ? new FilesystemScanner($tester)
            : new StdinScanner($tester);

        $scanner->scan(
            $paths,
            dirname(__DIR__, 4),
            $exclusions
        );

        $output->summary($tester);

        exit($tester->getExitCode());
    }

    /**
     * fetchs config
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    private static function getConfig(Filesystem $filesystem) : ConfigInterface
    {
        $configCandidates = [
            [
                "type"     => "json",
                "filename" => "query-validator.json"
            ],
            [
                "type"     => "yaml",
                "filename" => "query-validator.yml"
            ]
        ];
        $configFile = null;
        foreach ($configCandidates as $candidate) {
            if (file_exists($candidate['filename'])) {
                $configFile = $candidate;
                break;
            }
        }

        if ($configFile === null) {
            throw new ConfigException("no configuration file found");
        }

        switch ($configFile['type']) {
            case "json":
                $config = ConfigFactory::fromJson($filesystem, $configFile['filename']);
                break;
            case "yaml":
                $config = ConfigFactory::fromYaml($filesystem, $configFile['filename']);
                break;
            default:
                throw new ConfigException("unsupported configuration type '".$configFile['type']."'");
        }

        return $config;
    }

    /**
     * fetches paths to scan from configuration-data
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           ConfigInterface $config         configuration object to use
     */
    private function getPaths(ConfigInterface $config) : array
    {
        $paths = $config->get("paths");

        if ($paths === null) {
            $baseDirecory = dirname(__DIR__, 4);
            $paths = [ $baseDirecory ];
        }

        if (!is_array($paths)) {
            $paths = [ $paths ];
        }

        return $paths;
    }

    /**
     * fetches exclusions from configuration-data
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           ConfigInterface $config         configuration object to use
     */
    private function getExclusions(ConfigInterface $config) : array
    {
        $exclusions = $config->get("exclusions") ?? [];

        if (!is_array($exclusions)) {
            $exclusions = [ $exclusions ];
        }

        return $exclusions;
    }

    /**
     * fetches dump data from database dump
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           ConfigInterface $config         configuration object to use
     */
    private function getDumpData(ConfigInterface $config) : DumpData
    {
        $dumpFile = $config->get("dumpfile");

        if ($dumpFile === null) {
            return new DumpData;
        }

        if (\is_array($dumpFile)) {
            throw new DumpDataException("cannot convert array to string");
        }

        $dumpFile = strval($dumpFile);

        if (!file_exists($dumpFile)) {
            throw new DumpDataException("given dump file '".$dumpFile."' does not exist");
        }

        $filesystem = self::getFilesystem();
        return FromMysqlDump::getDumpData($filesystem, $dumpFile);
    }

    private function getFilesystem() : Filesystem
    {
        $adapter = new LocalFilesystemAdapter(dirname(__DIR__, 4));
        return new Filesystem($adapter);
    }
}
