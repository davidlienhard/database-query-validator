<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester\Tests;

class Parameters extends TestAbstract
{
    /**
     * starts the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : bool
    {
        $isValid = true;
        $allowedTypes = [ "s", "i", "d", "b" ];

        $query = $this->query->getQuery();

        // filter splat operators
        $parameters = array_filter(
            $this->query->getParameters(),
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
                $this->errors[] = "parameter '".$parameterCount."' is invalid";
            }
        }

        if ($numberOfQuestionmarks !== $numberOfDataParameters) {
            $this->errors[] = "number of question marks in query (".$numberOfQuestionmarks.") ".
                "or number of data parameters (".$numberOfDataParameters.") ";
            $isValid = false;
        }

        // fetch columns from query
        if (preg_match_all('/(?:(`([A-z0-9\-\_]+)`\.))?`([A-z0-9\-\_\$"\.\ "]+)`( |)(=|>=|<=|LIKE|!=|<=>)( |)\?/', $query, $matches)) {
            for ($columnNumber = 0; $columnNumber < count($matches[0] ?? []); $columnNumber++) {
                $tableName = $matches[2][$columnNumber] ?? "";
                $columnName = $matches[3][$columnNumber] ?? "";

                if ($tableName !== "" && $this->dumpData->getWithTable($tableName, $columnName) !== null) {
                    if ($this->dumpData->getWithTable($tableName, $columnName) !== ($types[$columnNumber] ?? "")) {
                        $this->errors[] = "given type '".($types[$columnNumber] ?? "")."' ".
                            "does not match dump type '".$this->dumpData->getWithTable($tableName, $columnName)."' ".
                            "in column `".$tableName."`.`".$columnName."`";
                        $isValid = false;
                    }
                } elseif ($this->dumpData->getWithoutTable($columnName) !== null) {
                    if ($this->dumpData->getWithoutTable($columnName) !== ($types[$columnNumber] ?? "")) {
                        $this->errors[] = "given type '".($types[$columnNumber] ?? "")."' ".
                            "does not match dump type '".$this->dumpData->getWithoutTable($columnName)."' ".
                            "in column `".$columnName."`";
                        $isValid = false;
                    }
                }//end if
            }//end for
        }//end if

        for ($i = 0; $i < strlen($types); $i++) {
            if (!in_array($types[$i], $allowedTypes, true)) {
                $this->errors[] = "invalid type supplied. '".$types[$i]."' given. must be '".implode(", ", $allowedTypes)."'";
                $isValid = false;
            }
        }

        return $isValid;
    }
}
