<?php
// Direct test of accomplishment creation
include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/database.php';
include __DIR__ . '/accomplishment.php';

echo "<h3>Direct Accomplishment Insert Test</h3>";

$database = new Database();
$db = $database->getConnection();

// Test values - CHANGE THESE TO MATCH YOUR DATABASE
$test_sdp_id = 3; // Change to a valid SDP ID
$test_r_matrix_id = 1; // Change to a valid office ID
$test_accomplishment = "75.5";
$test_evidence = "Test evidence";
$test_description = "Test description";

echo "<h4>Test Data:</h4>";
echo "SDP ID: $test_sdp_id<br>";
echo "Office ID: $test_r_matrix_id<br>";
echo "Accomplishment: $test_accomplishment<br>";
echo "Evidence: $test_evidence<br>";
echo "Description: $test_description<br><br>";

try {
    // Check if SDP exists
    $stmt = $db->prepare("SELECT sdp_id, kpi FROM tbl_sdp WHERE sdp_id = ?");
    $stmt->execute([$test_sdp_id]);
    if ($stmt->rowCount() == 0) {
        echo "<span style='color:red;'>❌ ERROR: SDP ID $test_sdp_id does not exist!</span><br>";
        echo "Please update the \$test_sdp_id variable in this file with a valid SDP ID.<br><br>";
        
        // Show available SDPs
        $stmt = $db->query("SELECT sdp_id, kpi FROM tbl_sdp LIMIT 5");
        echo "<strong>Available SDPs:</strong><br>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- ID: {$row['sdp_id']}, KPI: {$row['kpi']}<br>";
        }
        exit;
    }
    echo "✓ SDP exists<br>";
    
    // Check if office exists
    $stmt = $db->prepare("SELECT r_matrix_id, office_unit FROM tbl_responsibility_matrix WHERE r_matrix_id = ?");
    $stmt->execute([$test_r_matrix_id]);
    if ($stmt->rowCount() == 0) {
        echo "<span style='color:red;'>❌ ERROR: Office ID $test_r_matrix_id does not exist!</span><br>";
        echo "Please update the \$test_r_matrix_id variable in this file with a valid office ID.<br><br>";
        
        // Show available offices
        $stmt = $db->query("SELECT r_matrix_id, office_unit FROM tbl_responsibility_matrix LIMIT 5");
        echo "<strong>Available Offices:</strong><br>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- ID: {$row['r_matrix_id']}, Office: {$row['office_unit']}<br>";
        }
        exit;
    }
    echo "✓ Office exists<br><br>";
    
    // Try to create accomplishment
    echo "<h4>Creating Accomplishment...</h4>";
    
    $accomplishment = new Accomplishment($db);
    $accomplishment->sdp_id = $test_sdp_id;
    $accomplishment->target_id = null;
    $accomplishment->accomplishment = $test_accomplishment;
    $accomplishment->r_matrix_id = $test_r_matrix_id;
    $accomplishment->evidence = $test_evidence;
    $accomplishment->description = $test_description;
    
    if ($accomplishment->create()) {
        echo "<span style='color:green;'>✓ SUCCESS! Accomplishment created with ID: {$accomplishment->accomplishment_id}</span><br>";
        
        // Verify it was inserted
        $stmt = $db->prepare("SELECT * FROM tbl_accomplishment WHERE accomplishment_id = ?");
        $stmt->execute([$accomplishment->accomplishment_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h4>Inserted Data:</h4>";
        echo "<pre>" . print_r($row, true) . "</pre>";
        
        // Clean up test data
        echo "<br><a href='?cleanup={$accomplishment->accomplishment_id}'>Click here to delete this test record</a>";
        
    } else {
        echo "<span style='color:red;'>❌ FAILED: Could not create accomplishment</span><br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color:red;'>❌ ERROR: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Handle cleanup
if (isset($_GET['cleanup'])) {
    $id = intval($_GET['cleanup']);
    $stmt = $db->prepare("DELETE FROM tbl_accomplishment WHERE accomplishment_id = ?");
    if ($stmt->execute([$id])) {
        echo "<br><span style='color:green;'>✓ Test record deleted</span>";
    }
}
?>
