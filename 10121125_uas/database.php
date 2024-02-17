<?php

$DB_HOST = '127.0.0.1';
$DB_PORT = '3306';
$DB_USER = 'root';
$DB_PASSWORD = '';
$DB_DATABASE = 'kepegawaian';

function connectDb(){
    global $DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE, $DB_PORT;
    $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_DATABASE, $DB_PORT);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}
function closeDb($conn)
{
    mysqli_close($conn);
}


function findAll($table, $extra = "", $column = "*", $condition = false)
{
    $conn = connectDb();
    $q = "SELECT $column FROM $table";
    if ($extra) {
        $q .= " $extra";
    }
    if ($condition) {
        $q .= " WHERE $condition";
    }

    $result = mysqli_query($conn, $q);

    if (!$result) {
        die("Error in query: " . mysqli_error($conn));
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    closeDb($conn);
    return $rows;
}

function findById($table, $id, $column = "*")
{
    $conn = connectDb();
    $q = "SELECT $column FROM $table WHERE id = ?";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Error in query: " . mysqli_error($conn));
    }

    closeDb($conn);
    return $result;
}

function insert($table, $data)
{
    $conn = connectDb();
    $columns = implode(", ", array_keys($data));
    $values = array_values($data);
    $placeholders = str_repeat("?,", count($values) - 1) . "?";
    $q = "INSERT INTO $table ($columns) VALUES ($placeholders)";

    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, str_repeat("s", count($values)), ...$values);
    $result = mysqli_stmt_execute($stmt);

    closeDb($conn);
    return $result;
}
function update($table, $data, $id)
{
    $conn = connectDb();
    $setClause = implode(" = ?, ", array_keys($data)) . " = ?";
    $q = "UPDATE $table SET $setClause WHERE id = ?";

    $stmt = mysqli_prepare($conn, $q);
    $values = array_values($data);
    $types = str_repeat("s", count($values) + 1);
    $params = array_merge([$stmt, $types], $values, [$id]);

    call_user_func_array('mysqli_stmt_bind_param', $params);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        die("Error in query: " . mysqli_error($conn));
    }

    closeDb($conn);
    return $result;
}

function delete($table, $id)
{
    $conn = connectDb();
    $q = "DELETE FROM $table WHERE id = ?";

    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "i", $id);
    $result = mysqli_stmt_execute($stmt);

    if (!$result) {
        die("Error in query: " . mysqli_error($conn));
    }

    closeDb($conn);
    return $result;
}


