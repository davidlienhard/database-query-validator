<?php

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\Tester\Tests;

use DavidLienhard\Database\QueryValidator\Queries\Query;

class StrictInserts extends TestAbstract
{
    /**
     * starts the validation
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function validate() : bool
    {
        if ($this->query->getType() !== Query::TYPE_INSERT) {
            return true;
        }

        // get table name
        $query = $this->query->getQuery();
        $query = \preg_replace("/\\n/", " ", $query);

        if ($query === null) {
            $this->errors[] = "unable to remove newlines";
            return false;
        }

        $result = \preg_match("/^INSERT INTO([ ]+)`([A-z0-9\-\_]+)`/mi", trim($query), $matches);

        if ($result === false || $result === 0) {
            if (($this->options['ignoreMissingTablenames'] ?? false) === true) {
                return true;
            }

            $this->errors[] = "tablename could not be found in query";
            return false;
        }

        $tableName = $matches[2];

        // get all colums from table
        $columns = $this->dumpData->getColumsForTable($tableName);

        if ($columns === null) {
            $this->errors[] = "no colums for table '".$tableName."' found in dump";
            return false;
        }

        // get names of colums the must be in insert
        // text-colums that are not nullable
        $requiredColumns = array_filter(
            $columns,
            fn ($c) => $c->isText() && !$c->isNull()
        );

        unset($matches);
        $result = \preg_match_all("/`([A-z0-9\-\_]+)`( |)=( |)/", trim($query), $matches);

        if ($result === false || $result === 0) {
            $this->errors[] = "could not find colums in current query";
            return false;
        }

        $columnsInQuery = $matches[1] ?? [];

        $isValid = true;
        foreach ($requiredColumns as $requiredColumn) {
            if (!in_array($requiredColumn->getName(), $columnsInQuery, true)) {
                $this->errors[] = "column '".$requiredColumn->getName()."' is missing in insert";
                $isValid = false;
            }
        }

        return $isValid;
    }
}
