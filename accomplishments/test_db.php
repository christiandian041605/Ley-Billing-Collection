<?php
// Simple test script to verify database connection and table structure
include_once __DIR__ . '/../config/database.php';

echo "<h3>Database Connection Test</h3>";

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "✓ Database connection successful<br><br>";
    
    // Check if accomplishment table exists and show structure
    echo "<h4>Table: tbl_accomplishment</h4>";
    $stmt = $db->query("DESCRIBE tbl_accomplishment");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // Check if there are SDPs
    echo "<h4>Available SDPs</h4>";
    $stmt = $db->query("SELECT sdp_id, kpi FROM tbl_sdp LIMIT 5");
    $sdps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($sdps) > 0) {
        echo "✓ Found " . count($sdps) . " SDPs<br>";
        foreach ($sdps as $sdp) {
            echo "- SDP #{$sdp['sdp_id']}: {$sdp['kpi']}<br>";
        }
    } else {
        echo "⚠ No SDPs found in database<br>";
    }
    echo "<br>";
    
    // Check if there are offices
    echo "<h4>Available Offices (Responsibility Matrix)</h4>";
    $stmt = $db->query("SELECT r_matrix_id, office_unit FROM tbl_responsibility_matrix LIMIT 5");
    $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($offices) > 0) {
        echo "✓ Found " . count($offices) . " offices<br>";
        foreach ($offices as $office) {
            echo "- Office #{$office['r_matrix_id']}: {$office['office_unit']}<br>";
        }
    } else {
        echo "⚠ No offices found in database<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
