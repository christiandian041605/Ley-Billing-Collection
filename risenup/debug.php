<?php
include_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$focus_area = 'RESEARCH, TECHNOLOGY AND INNOVATION';

// Exact same query as index.php
$query = "SELECT DISTINCT s.sdp_id, s.kpi, s.initiatives, s.objectives_id,
          o.objectives_details, o.focus_area
          FROM tbl_sdp s
          LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id
          WHERE o.focus_area = :focus_area
          ORDER BY s.kpi";

$stmt = $db->prepare($query);
$stmt->execute(['focus_area' => $focus_area]);
$sdps = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/plain');
echo "=== RISENUP Debug Output ===\n\n";
echo "Focus Area: $focus_area\n";
echo "Query returned: " . count($sdps) . " SDPs\n\n";

foreach ($sdps as $index => $sdp) {
    echo "[$index] SDP #{$sdp['sdp_id']}: {$sdp['kpi']}\n";
    echo "     Initiatives: " . substr($sdp['initiatives'], 0, 50) . "...\n";
    
    // Get offices
    $office_query = "SELECT DISTINCT rm.office_unit
                    FROM tbl_sdp_responsibility sr
                    LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id
                    WHERE sr.sdp_id = :sdp_id
                    ORDER BY rm.office_unit";
    $office_stmt = $db->prepare($office_query);
    $office_stmt->execute(['sdp_id' => $sdp['sdp_id']]);
    $offices = $office_stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "     Offices: " . implode(', ', $offices) . "\n\n";
}

echo "\n=== Check for Array Key Duplicates ===\n";
$sdp_ids = array_column($sdps, 'sdp_id');
$unique_ids = array_unique($sdp_ids);

if (count($sdp_ids) === count($unique_ids)) {
    echo "✓ No duplicate sdp_id values in result array\n";
} else {
    echo "⚠️ DUPLICATE sdp_id values found:\n";
    echo "Total: " . count($sdp_ids) . " | Unique: " . count($unique_ids) . "\n";
    $duplicates = array_diff_assoc($sdp_ids, $unique_ids);
    print_r($duplicates);
}
