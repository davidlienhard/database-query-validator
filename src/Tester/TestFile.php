<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use DavidLienhard\Database\QueryValidator\Queries\QueryInterface;
use DavidLienhard\Database\QueryValidator\Tester\PhpNodeVisitor;
use DavidLienhard\Database\QueryValidator\Tester\TestFileInterface;
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
     * @param           OutputInterface $output         output object to use
     * @param           DumpData        $dumpData       data from the database-dump
     */
    public function __construct(private string $file, private OutputInterface $output, private DumpData $dumpData)
    {
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
                $queryString = $query->getQuery();
                $parameters = $query->getParameters();

                $hasError = $this->checkPreparedStatement(
                    $query->getFilename(),
                    $query->getLinenumber(),
                    $queryString,
                    ...$parameters
                ) ? $hasError : true;
            }

            if (!$this->ignoresyntax) {
                $testResult = $this->runTest(SyntaxTest::class, $query, "invalid syntax");
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
     * checks if a prepared statement is valid
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $file       the file containing the query
     * @param           int                 $linenumber line where the query starts
     * @param           string              $query      query to analyze
     * @param           string              $parameters data paramaters
     */
    private function checkPreparedStatement(string $file, int $linenumber, string $query, string ...$parameters) : bool
    {
        $isValid = true;
        $allowedTypes = [ "s", "i", "d", "b" ];

        // filter splat operators
        $parameters = array_filter(
            $parameters,
            fn ($p) => substr($p, 0, 3) !== "..."
        );

        $numberOfQuestionmarks = substr_count($query, "?");
        $numberOfDataParameters = count($parameters);

        // validate parameters and get types
        $types = "";
        $parameterCount = 0;

        foreach ($parameters as $parameter) {
            $parameterCount++;
            $regex = "/^new DBParam\(\"(".implode("|", $allowedTypes).")\", (.*)\)$/";
            if (preg_match($regex, trim($parameter), $matches)) {
                $types .= $matches[1];
            } else {
                $types .= "-";
                $this->addError(
                    $file,
                    $linenumber,
                    "parameter '".$parameterCount."' is invalid"
                );
            }
        }

        if ($numberOfQuestionmarks !== $numberOfDataParameters) {
            $this->addError(
                $file,
                $linenumber,
                "number of question marks in query (".$numberOfQuestionmarks.") ".
                "or number of data parameters (".$numberOfDataParameters.") "
            );
            $isValid = false;
        }

        // fetch columns from query
        if (preg_match_all('/(?:(`([A-z0-9\-\_]+)`\.))?`([A-z0-9\-\_\$"\.\ "]+)`( |)(=|>=|<=|LIKE|!=|<=>)( |)\?/', $query, $matches)) {
            for ($columnNumber = 0; $columnNumber < count($matches[0] ?? []); $columnNumber++) {
                $tableName = $matches[2][$columnNumber] ?? "";
                $columnName = $matches[3][$columnNumber] ?? "";

                if ($tableName !== "" && $this->dumpData->getWithTable($tableName, $columnName) !== null) {
                    if ($this->dumpData->getWithTable($tableName, $columnName) !== ($types[$columnNumber] ?? "")) {
                        $this->addError(
                            $file,
                            $linenumber,
                            "given type '".($types[$columnNumber] ?? "")."' ".
                            "does not match dump type '".$this->dumpData->getWithTable($tableName, $columnName)."' ".
                            "in column `".$tableName."`.`".$columnName."`"
                        );
                        $isValid = false;
                    }
                } elseif ($this->dumpData->getWithoutTable($columnName) !== null) {
                    if ($this->dumpData->getWithoutTable($columnName) !== ($types[$columnNumber] ?? "")) {
                        $this->addError(
                            $file,
                            $linenumber,
                            "given type '".($types[$columnNumber] ?? "")."' ".
                            "does not match dump type '".$this->dumpData->getWithoutTable($columnName)."' ".
                            "in column `".$columnName."`"
                        );
                        $isValid = false;
                    }
                }//end if
            }//end for
        }//end if

        for ($i = 0; $i < strlen($types); $i++) {
            if (!in_array($types[$i], $allowedTypes, true)) {
                $this->addError(
                    $file,
                    $linenumber,
                    "invalid type supplied. '".$types[$i]."' given. must be '".implode(", ", $allowedTypes)."'"
                );
                $isValid = false;
            }
        }

        return $isValid;
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

    private function runTest(string $className, QueryInterface $query, string $errorPrefix = "") : bool
    {
        $tester = new $className($query, $this->dumpData);
        $tester->validate();
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

        return $errorCount > 0;
    }
}
