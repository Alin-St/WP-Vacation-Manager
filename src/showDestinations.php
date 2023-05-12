<?php
require_once 'utils/configuration.php';

// Get the query parameters from the query string
$country_name = $_GET['country_name'] ?? '';
$count_only = isset($_GET['count']);
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * 4;

// Build the SQL query with the WHERE clause for the country_name filter and the LIMIT and OFFSET clauses for pagination
$sql_query = $count_only ?
    "SELECT COUNT(*) FROM destinations WHERE country_name LIKE ?" :
    "SELECT * FROM destinations WHERE country_name LIKE ? LIMIT 4 OFFSET ?";

// Prepare the statement with the connection object and the query string
global $connection;
$stmt = mysqli_prepare($connection, $sql_query);
if (!$stmt) {
    die('Error: ' . mysqli_error($connection));
}

// Bind the parameters to the statement
$search_string = "%$country_name%";
if ($count_only) {
    mysqli_stmt_bind_param($stmt, "s", $search_string);
} else {
    mysqli_stmt_bind_param($stmt, "si", $search_string, $offset);
}

// Execute the statement and check for errors
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die('Error: ' . mysqli_error($connection));
}

if ($count_only) {
    $row = mysqli_fetch_row($result);
    $count = (int) $row[0];

    // Return the number of matching destinations.
    echo $count;
}
else {
    // Loop over the result set and build the array of matching destinations
    $requested_destinations = array();
    while ($row = mysqli_fetch_array($result)) {
        $requested_destinations[] = array(
            $row['id'],
            $row['location_name'],
            $row['country_name'],
            $row['description'],
            $row['tourist_targets'],
            $row['estimated_cost_per_day']
        );
    }

    // Return the matching destinations for the requested page
    echo json_encode($requested_destinations);
}

// Cleanup.
mysqli_stmt_close($stmt);
mysqli_close($connection);
