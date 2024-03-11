<?php
require_once('connect.php');
require_once("autoload.php");

$db = new Db;

function debug($var)
{
    print_r('<pre>');
    var_dump($var);
    print_r('</pre>');
}

// уникальные занятия

$db->query('SELECT min(id), start_time, session_configuration_id
FROM `sessions`
GROUP BY start_time, session_configuration_id');
$uniqueSessions = $db->resultSet();

// находим дубли

$db->query('WITH UNQ as (
    SELECT min(id) as id
    FROM `sessions`
    GROUP BY start_time, session_configuration_id
),

JT AS (
    SELECT s.start_time, s.session_configuration_id, sm.client_id, sm.session_id
    FROM session_members AS sm
             JOIN sessions AS s
                  ON s.id = sm.session_id
)

SELECT * from `JT`
WHERE JT.session_id not in (
    SELECT id FROM UNQ
);');

// меняем id дублей на id уникальных занятий

$duplicatesResult = $db->resultSet();

foreach ($duplicatesResult as $duplicatesItem) {
    foreach ($uniqueSessions as $uniqueSession) {
        if ($duplicatesItem->start_time == $uniqueSession->start_time &&
            $duplicatesItem->session_configuration_id == $uniqueSession->session_configuration_id) {

            $db->query(
                'UPDATE session_members
                    SET session_id = :uniqueSessionId
                    WHERE session_id = :duplicatesItemId;');
            $db->bind(':uniqueSessionId', get_object_vars($uniqueSession)["min(id)"]);
            $db->bind(':duplicatesItemId',$duplicatesItem->session_id);
            $db->execute();
        }
    }
}

$db->query('WITH duplicates AS (
    SELECT id, start_time, session_configuration_id, ROW_NUMBER() OVER(
        PARTITION BY start_time, session_configuration_id
        ) AS rownum
    FROM sessions
)
SELECT * FROM duplicates WHERE duplicates.rownum > 1;');
$sessionDuplicates = $db->resultSet();

foreach ($sessionDuplicates as $row) {
    $db->query('DELETE FROM sessions WHERE id = :id;');
    $db->bind(':id', $row->id);
    $db->execute();
}

$db->query('WITH duplicates AS (
    SELECT id, ROW_NUMBER() OVER(
        PARTITION BY session_id, client_id
        ) AS rownum
    FROM session_members
)
SELECT * FROM duplicates WHERE duplicates.rownum > 1;');
$sessionMembersDuplicates = $db->resultSet();

foreach ($sessionMembersDuplicates as $row) {
    $db->query('DELETE FROM sessions WHERE id = :id;');
    $db->bind(':id', $row->id);
    $db->execute();
}



