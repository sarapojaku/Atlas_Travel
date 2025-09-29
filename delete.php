<?php
include 'db_connect.php';

if (isset($_GET['id']) && isset($_GET['table'])) {
    $id = intval($_GET['id']); 
    $table = $_GET['table'];

    $primaryKeys = [
        'country' => 'CountryID',
        'client' => 'ClientID',
        'destination' => 'DestinationID',
        'staff' => 'StaffID'
    ];

    if (!array_key_exists($table, $primaryKeys)) die('Invalid table');

    $pk = $primaryKeys[$table];
    $stmt = $conn->prepare("DELETE FROM $table WHERE $pk = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Map table → section hash
$hashes = [
    'staff' => 'staff',
    'country' => 'countries',
    'client' => 'clients',
    'destination' => 'destinations'
];

$hash = $hashes[$table] ?? 'dashboard';
header("Location: admin.php#$hash");
exit;
?>
