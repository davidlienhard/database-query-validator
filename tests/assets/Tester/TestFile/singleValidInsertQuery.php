<?php
declare(strict_types=1);

use DavidLienhard\Database\Parameter as DBParam;

$db->query(
    "INSERT INTO
        `user`
    SET
        `userName` = ?,
        `userMail` = ?",
    new DBParam("s", $userName),
    new DBParam("s", $userMail)
);
