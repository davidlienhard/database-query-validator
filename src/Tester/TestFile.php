<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester;

use \DavidLienhard\Database\QueryValidator\Tester\TestFileInterface;
use \DavidLienhard\Database\QueryValidator\Output\OutputInterface;
use \DavidLienhard\Database\QueryValidator\DumpData\DumpData;
use \PhpMyAdmin\SqlParser\Lexer;
use \PhpMyAdmin\SqlParser\Parser;
use \PhpMyAdmin\SqlParser\Utils\Error as SqlParserError;
use \PhpParser\ParserFactory;
use \PhpParser\NodeTraverser;
use \PhpParser\Error as PhpParserError;

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
     * @author          David Lienhard <david@lienhard.win>
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
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : void
    {
        try {
            $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
            $traverser = new NodeTraverser;
            $visitor = new Visitor;
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

            // check if use statment is set
            $hasUseStmt = strpos(
                $fileContent,
                "use \\DavidLienhard\\Database\\Parameter as DBParam;"
            ) !== false;
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
                $visitor->getQueries(),
                $hasUseStmt
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
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string              $file       the file containing the queries
     * @param           array               $queries    the queries to validate
     * @param           bool                $hasUseStmt whether this file has the use statement
     * @throws          \Exception                      if the file does not exist
     * @uses            self::addError()
     * @uses            self::checkPreparedStatement()
     * @uses            self::checkMysqlSyntax()
     * @uses            self::$queryCount
     */
    public function validateQueries(string $file, array $queries, bool $hasUseStmt) : void
    {
        $hasPrepared = false;

        foreach ($queries as $query) {
            $hasError = false;
            $argumentCount = count($query['data'] ?? []);
            $isPrepared = $argumentCount > 1;

            if ($isPrepared) {
                $hasPrepared = true;
            }

            if ($isPrepared) {
                $queryString = $query['data'][0];
                unset($query['data'][0]);
                $parameters = array_values($query['data']);

                $hasError = $this->checkPreparedStatement(
                    $file,
                    $query['line'],
                    $queryString,
                    ...$parameters
                ) ? $hasError : true;
            }

            if (!$this->ignoresyntax && self::checkMysqlSyntax($query['data'][0] ?? "") !== true) {
                $this->addError($file, $query['line'], "invalid sql syntax");
                $hasError = true;
            }

            if (!$hasUseStmt && $hasPrepared) {
                $hasError = true;
            }

            $this->output->query($file, $query['line'], !$hasError);
            $this->queryCount++;
        }//end foreach

        if (!$hasUseStmt && $hasPrepared) {
            $this->addError(
                $file,
                0,
                "has prepared statements but not use statement"
            );
        }
    }

    /**
     * checks if a prepared statement is valid
     *
     * @author          David Lienhard <david@lienhard.win>
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

                if ($tableName != "" && $this->dumpData->getWithTable($tableName, $columnName) !== null) {
                    if ($this->dumpData->getWithTable($tableName, $columnName) != ($types[$columnNumber] ?? "")) {
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
                    if ($this->dumpData->getWithoutTable($columnName) != ($types[$columnNumber] ?? "")) {
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
            if (!in_array($types[$i], $allowedTypes)) {
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
     * checks if the syntax of a query is valid
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $query      query to analyze
     * @return          bool|array                  true or a list of errors
     * @uses            \PhpMyAdmin\SqlParser\Lexer
     * @uses            \PhpMyAdmin\SqlParser\Parser
     * @uses            \PhpMyAdmin\SqlParser\Utils\Error
     */
    public static function checkMysqlSyntax(string $query) : bool | array
    {
        $query = trim($query, " \t\n\r\0\x0B\"");

        $from = [ "/\\\\n/", "/\?/" ];  // replace \n (as string) with newlines and questionmarks with 1 for validation
        $to = [ "\n", 1 ];
        $query = \preg_replace($from, $to, $query);

        if ($query === null) {
            throw new \Exception("unable to read query");
        }

        $lexer = new Lexer($query, false);
        $parser = new Parser($lexer->list);
        $errors = SqlParserError::get([$lexer, $parser]);
        if (count($errors) === 0) {
            return true;
        }
        return SqlParserError::format($errors);
    }

    /**
     * adds an error to the internal error list
     *
     * @author          David Lienhard <david@lienhard.win>
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
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrorcount() : int
    {
        return count($this->errors);
    }

    /**
     * returns the number of queries scanned
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getQuerycount() : int
    {
        return $this->queryCount;
    }

    /**
     * returns the list of errors
     *
     * @author          David Lienhard <david@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
