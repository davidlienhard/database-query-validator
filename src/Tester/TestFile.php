<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use DavidLienhard\Database\QueryValidator\Config\ConfigInterface;
use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use DavidLienhard\Database\QueryValidator\Queries\QueryInterface;
use DavidLienhard\Database\QueryValidator\Tester\PhpNodeVisitor;
use DavidLienhard\Database\QueryValidator\Tester\TestFileInterface;
use DavidLienhard\Database\QueryValidator\Tester\Tests\Parameters as ParametersTest;
use DavidLienhard\Database\QueryValidator\Tester\Tests\StrictInserts as StrictInsertsTest;
use DavidLienhard\Database\QueryValidator\Tester\Tests\Syntax as SyntaxTest;
use PhpParser\Error as PhpParserError;
use PhpParser\NodeTraverser as PhpNodeTraverser;
use PhpParser\ParserFactory as PhpParserFactory;

class TestFile implements TestFileInterface
{
    private bool $ignoresyntax = false;

    /**
     * list of errors that occurred
     * @var     string[]
     */
    private array $errors = [];

    /** number of query that have been checked */
    private int $queryCount = 0;

    /**
     * sets dependencies
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           path to the file to validate
     * @param           ConfigInterface $config         config object to use
     * @param           OutputInterface $output         output object to use
     * @param           DumpData        $dumpData       data from the database-dump
     */
    public function __construct(
        private string $file,
        private ConfigInterface $config,
        private OutputInterface $output,
        private DumpData $dumpData
    ) {
        if (!file_exists($file)) {
            throw new \Exception("file '".$file."' does not exist");
        }
    }

    /**
     * starts the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : void
    {
        try {
            $parser = (new PhpParserFactory)->create(PhpParserFactory::PREFER_PHP7);
            $traverser = new PhpNodeTraverser;
            $visitor = new PhpNodeVisitor($this->file);
            $traverser->addVisitor($visitor);

            $fileContent = \file_get_contents($this->file);

            if ($fileContent === false) {
                throw new \Exception("unable to read contents of file '".$this->file."'");
            }

            $ast = $parser->parse($fileContent);

            if ($ast === null) {
                throw new \Exception("unable to parse file '".$this->file."'");
            }

            $stmts = $traverser->traverse($ast);
        } catch (PhpParserError $error) {
            throw new \Exception(
                "Parse error: ".$error->getMessage()." (".$this->file.")",
                $error->getCode(),
                $error
            );
        } catch (\Throwable $t) {
            throw new \Exception(
                "unknown error: ".$t->getMessage()." (".$this->file.")",
                $t->getCode(),
                $t
            );
        }//end try

        try {
            $this->validateQueries(
                $this->file,
                $visitor->getQueries()
            );
        } catch (\Throwable $t) {
            throw new \Exception(
                "error: ".$t->getMessage()." (".$this->file.")",
                $t->getCode(),
                $t
            );
        }

        unset($parser, $traverser, $visitor, $ast, $stmts);
    }

    /**
     * validates a list of queries
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $file       the file containing the queries
     * @param           array<\DavidLienhard\Database\QueryValidator\Queries\QueryInterface>    $queries    the queries to validate
     * @throws          \Exception                      if the file does not exist
     * @uses            self::addError()
     * @uses            self::checkPreparedStatement()
     * @uses            self::$queryCount
     */
    public function validateQueries(string $file, array $queries) : void
    {
        foreach ($queries as $query) {
            $hasError = false;

            if ($query->isPrepared()) {
                $testResult = $this->runTest(ParametersTest::class, $query);
                $hasError = $testResult === false ? true : $hasError;
            }

            if (!$this->getConfigParameter("ignoresyntax")) {
                $testResult = $this->runTest(SyntaxTest::class, $query, "invalid syntax");
                $hasError = $testResult === false ? true : $hasError;
            }

            if ($this->getConfigParameter("strictinserts")) {
                $options = [
                    "ignoreMissingTablenames" => $this->getConfigParameter("strictinsertsignoremissingtablenames")
                ];
                $testResult = $this->runTest(StrictInsertsTest::class, $query, "", $options);
                $hasError = $testResult === false ? true : $hasError;
            }

            $this->output->query(
                $query->getFilename(),
                $query->getLinenumber(),
                !$hasError
            );

            $this->queryCount++;
        }//end foreach
    }

    /**
     * adds an error to the internal error list
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $file       file where the error occured
     * @param           int                 $line       linenumber of the beginning of the query
     * @param           string              $error      error description
     */
    private function addError(string $file, int $line, string $error): void
    {
        $this->errors[] = "error in file '".$file.":".$line."' ".$error;
    }

    /**
     * returns the number of errors
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int
    {
        return count($this->errors);
    }

    /**
     * returns the number of queries scanned
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQuerycount() : int
    {
        return $this->queryCount;
    }

    /**
     * returns the list of errors
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    private function runTest(string $className, QueryInterface $query, string $errorPrefix = "", array $options = []) : bool
    {
        $tester = new $className($query, $this->dumpData, $options);
        $result = $tester->validate();
        $errors = $tester->getErrors();
        $errorCount = $tester->getErrorcount();
        unset($tester);

        foreach ($errors as $error) {
            $this->addError(
                $query->getFilename(),
                $query->getLinenumber(),
                $errorPrefix." '".$error."'"
            );
        }

        return $result;
    }


    /**
     * returns the value of a paramater from the config
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $parameterName  name of the parameter to fetch
     * @param           bool                $default        value to return if parameter is not found in config
     */
    private function getConfigParameter(string $parameterName, bool $default = false) : bool
    {
        try {
            return (bool) $this->config->get("parameters", $parameterName);
        } catch (\Exception $e) {
            return $default;
        }
    }
}
