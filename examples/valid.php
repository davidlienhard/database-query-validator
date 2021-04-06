<?php

declare(strict_types=1);

use \DavidLienhard\Database\Mysql;
use \DavidLienhard\Database\Parameter as DBParam;

$db = new Mysql(
    "host",
    "user",
    "pass",
    "database"
);


$db->query(
    "SELECT
        `userName`,
        `userMail`
    FROM
        `user`
    WHERE
        `userID` = ? and
        `userType` = ?",
    new DBParam("i", 1),
    new DBParam("s", "admin")
);
