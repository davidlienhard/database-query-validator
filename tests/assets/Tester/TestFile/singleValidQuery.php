<?php
declare(strict_types=1);

use DavidLienhard\Database\Parameter as DBParam;

$db->query(
    "SELECT
        `userID`,
        `userName`,
        `userMail`
    FROM
        `user`
    WHERE
        `userLevel` = ?",
    new DBParam("i", $userLevel)
);
