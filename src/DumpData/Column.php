<?php
/**
 * contains Column class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\DumpData;

use DavidLienhard\Database\QueryValidator\DumpData\ColumnInterface;

/**
 * object that will hold a column
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Column implements ColumnInterface
{
    /**
     * creates the column-object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $table          name of the table this column belongs to
     * @param           string          $name           name of this column
     * @param           string          $type           type of this column
     * @param           bool            $isNull         whether this column may be null or not
     * @param           bool            $isText         whether this column is text or not
     */
    public function __construct(
        private string $table,
        private string $name,
        private string $type,
        private bool $isNull,
        private bool $isText
    ) {
    }

    /**
     * returns the name of the table this column belongs to
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getTable() : string
    {
        return $this->table;
    }

    /**
     * returns the name of this column
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * returns the type of this column
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * returns whether this is a column my be null or not
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function isNull() : bool
    {
        return $this->isNull;
    }

    /**
     * returns whether this is a text column or not
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function isText() : bool
    {
        return $this->isText;
    }
}
