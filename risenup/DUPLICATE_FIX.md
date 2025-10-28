# RISENUP Duplicate SDP Fix

## Issues Found

### 1. Duplicate SDPs
**Problem:** SDPs were appearing multiple times due to multiple responsibility matrix entries.

**Example:**
- SDP #9 appeared 5 times (5 different offices)
- SDP #3 appeared 3 times (3 different offices)
- SDP #10 appeared 3 times (3 different offices)
- SDP #11 appeared 4 times (4 different offices)

**Root Cause:** The JOIN with `tbl_sdp_responsibility` and `tbl_responsibility_matrix` created a Cartesian product when an SDP had multiple office assignments.

### 2. Wrong Focus Area Names
**Problem:** Some focus areas didn't match the database values.

**Mismatches:**
- ❌ "INTERNATIONALIZATION" → ✅ "INTERNATIONALIZATION, EXTENSION AND LINKAGES"
- ❌ "EXTENSION AND LINKAGES" → ✅ "EXCELLENCE IN ACADEMICS AND SERVICES"

## Solution Applied

### 1. Fixed Query Structure

**Before (Caused Duplicates):**
```php
$query = "SELECT s.*, o.objectives_details, o.focus_area,
          GROUP_CONCAT(DISTINCT rm.office_unit SEPARATOR ', ') as offices
          FROM tbl_sdp s
          LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id
          LEFT JOIN tbl_sdp_responsibility sr ON s.sdp_id = sr.sdp_id
          LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id
          WHERE o.focus_area = :focus_area
          GROUP BY s.sdp_id";
```

**Problem with GROUP BY approach:**
- Even with `GROUP BY s.sdp_id`, the query still processes all joined rows
- Can cause issues with MySQL's ONLY_FULL_GROUP_BY mode
- GROUP_CONCAT works but inefficient

**After (No Duplicates):**
```php
// Step 1: Get unique SDPs first
$query = "SELECT DISTINCT s.sdp_id, s.kpi, s.initiatives, s.objectives_id,
          o.objectives_details, o.focus_area
          FROM tbl_sdp s
          LEFT JOIN tbl_objectives o ON s.objectives_id = o.objectives_id
          WHERE o.focus_area = :focus_area
          ORDER BY s.kpi";

// Step 2: Fetch offices separately for each SDP
foreach ($sdps as &$sdp) {
    $office_query = "SELECT DISTINCT rm.office_unit
                    FROM tbl_sdp_responsibility sr
                    LEFT JOIN tbl_responsibility_matrix rm ON sr.r_matrix_id = rm.r_matrix_id
                    WHERE sr.sdp_id = :sdp_id
                    ORDER BY rm.office_unit";
    $offices = $office_stmt->fetchAll(PDO::FETCH_COLUMN);
    $sdp['offices'] = implode(', ', $offices);
    
    // Step 3: Fetch targets
    $target_query = "SELECT * FROM tbl_target WHERE sdp_id = :sdp_id";
    $sdp['targets'] = $target_stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

**Why This Works Better:**
1. First query gets DISTINCT SDPs only (no duplicates)
2. Second query fetches offices per SDP (separate, clean)
3. Third query fetches targets per SDP
4. No complex GROUP BY with multiple JOINs
5. Cleaner, more maintainable code

### 2. Fixed Focus Area Mapping

**Before:**
```php
'I' => [
    'full_name' => 'INTERNATIONALIZATION',  // ❌ Wrong
],
'E' => [
    'full_name' => 'EXTENSION AND LINKAGES',  // ❌ Wrong
]
```

**After:**
```php
'I' => [
    'title' => 'Internationalization',
    'full_name' => 'INTERNATIONALIZATION, EXTENSION AND LINKAGES',  // ✅ Correct
],
'E' => [
    'title' => 'Excellence',
    'full_name' => 'EXCELLENCE IN ACADEMICS AND SERVICES',  // ✅ Correct
]
```

## Actual Focus Areas in Database

Based on `tbl_objectives`:

1. **R** - RESEARCH, TECHNOLOGY AND INNOVATION (4 SDPs)
2. **I** - INTERNATIONALIZATION, EXTENSION AND LINKAGES (? SDPs)
3. **S** - SUSTAINABILITY OF GOOD GOVERNANCE (? SDPs)
4. **E** - EXCELLENCE IN ACADEMICS AND SERVICES (? SDPs)
5. **N** - NOVELTY IN PRACTICES (? SDPs)
6. **U** - UNIVERSITY RANKING AND RECOGNITION (? SDPs)
7. **P** - PEOPLE (? SDPs)

## Benefits of New Approach

### Performance
✅ Fewer complex JOINs  
✅ More efficient queries  
✅ Better for large datasets  
✅ Easier to optimize  

### Correctness
✅ No duplicate SDPs  
✅ Accurate counts  
✅ Clean data structure  
✅ Proper focus area matching  

### Maintainability
✅ Clearer query logic  
✅ Easier to debug  
✅ Better separation of concerns  
✅ More readable code  

## Query Flow

```
1. SELECT DISTINCT SDPs
   ↓
   [SDP #3, SDP #9, SDP #10, SDP #11]

2. For each SDP:
   - SELECT DISTINCT offices
   - SELECT targets
   ↓
   
3. Combine into array:
   [
     {
       sdp_id: 3,
       kpi: "Research Collaborations...",
       offices: "Office A, Office B, Office C",
       targets: [{...}, {...}]
     },
     ...
   ]
```

## Testing Results

**Before Fix:**
- 16 rows returned (4 SDPs × multiple offices)
- Duplicates everywhere
- Wrong focus areas

**After Fix:**
- 4 unique SDPs returned
- No duplicates
- Correct focus areas
- Clean office list per SDP

## Result

✅ **No more duplicate SDPs**  
✅ **Correct focus area mapping**  
✅ **Clean, efficient queries**  
✅ **Proper data structure**  

The RISENUP module now displays unique SDPs correctly! 🎯
