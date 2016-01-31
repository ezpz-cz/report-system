<?php

if ($_GET["pw"] != "ASDFTHLPQER421sd45887dfgdh")
    die("pw not set!");

include_once(dirname(__FILE__)."/../scripts-generic/getPDO.php");
include_once(dirname(__FILE__)."/../scripts-generic/PDOQuery.php");

$pdo = getPDOConnection();

$query = "SELECT a.name, a.id AS admin_id FROM `soe-csgo`.`sb_admins` AS a WHERE a.active = 1";
$result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);

$table_active = "<table>
<thead>
    <tr>
        <th>admin</th>
        <th>nové</th>
        <th>řeší se</th>
        <th>hotové</th>
        <th>hotové – přijato</th>
        <th>hotové – přijato, ban</th>
        <th>hotové – zamítnuto</th>
    </tr>
</thead>
<tbody>";

for ($i = 0; $i < count($result); $i++)
{
    $table_active .= "<tr>
        <td>" . $row["name"] .  "</td>
        <td>new_" . $row["admin_id"] . "</td>
        <td>progress_" . $row["admin_id"] . "</td>
        <td>finished_" . $row["admin_id"] . "</td>
        <td>finished_accept" . $row["admin_id"] . "</td>
        <td>finished_ban" . $row["admin_id"] . "</td>
        <td>finished_rejected" . $row["admin_id"] . "</td>";
}

print_r($result);

foreach ($result as $row)
{
    $table_active .= "<tr><td>" . $row["name"] .  "</td>";
    $query = "SELECT s.id, COUNT(r.id) AS s_count FROM `ezpz-report-g`.`report_report` AS r
                JOIN `ezpz-report-g`.`report_status` as s ON s.id = r.status_id
                WHERE r.admin_id = :admin_id
                GROUP BY status_id, admin_id
                ORDER BY s.id ASC";
    $result_count = getPDOParametrizedQueryResult($pdo, $query, array(":admin_id" => $row["admin_id"]), __FILE__, __LINE__);

    echo "admin: " . $row["name"] . "<br />";
    print_r($result_count);
    echo "<br />";

    for ($i = 1; $i <= 5; $i++)
    {
        echo $i;
        if (array_key_exists(strval($i), $result_count))
        {
            $table_active .= "<td>" . $result_count[$i]["s_count"] . "</td>";
        }
        else
        {
            $table_active .= "<td>0</td>";
        }
    }

    $table_active .= "</tr>";
}

$table_active .= "</tbody></table>";

/*$query = "SELECT a.name, a.id FROM `soe-csgo`.`sb_admins` AS a WHERE a.active = 0 AND a.name <> 'test-admin'";
$result = getPDOQueryResult($pdo, $query, __FILE__, __LINE__);
$inactive_admins = array();

foreach ($result as $row)
{
    $inactive_admins[] = $row["name"];
}

//print_r($inactive_admins);*/

echo $table_active;

