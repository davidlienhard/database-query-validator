<?php
/**
 * contains ColumnInterface
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Database\QueryValidator\DumpData;

/**
 * interface for a column class
 *
 * @category        Database Query Validator
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
interface ColumnInterface
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
        string $table,
        string $name,
        string $type,
        bool $isNull,
        bool $isText
    );

    /**
     * returns the name of the table this column belongs to
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getTable() : string;

    /**
     * returns the name of this column
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getName() : string;

    /**
     * returns the type of this column
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getType() : string;

    /**
     * returns whether this is a column my be null or not
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function isNull() : bool;

    /**
     * returns whether this is a text column or not
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function isText() : bool;
}
