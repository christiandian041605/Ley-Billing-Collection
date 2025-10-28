# Accomplishments Module

## Overview
This module allows administrators to manage accomplishments for Strategic Development Plans (SDPs). The workflow follows the logical flow defined in the system documentation.

## Workflow for Administrators

1. **Select SDP**: Choose from the list of available SDPs (KPIs)
2. **Filter by Year** (Optional): Filter targets by specific year
3. **Filter by Quarter** (Optional): Filter targets by specific quarter (Q1, Q2, Q3, Q4)
4. **Select Target** (Optional): Choose a specific target associated with the SDP
5. **Select Responsibility Matrix**: Choose the office/unit responsible for this accomplishment
6. **Enter Accomplishment**: Input the actual value achieved
7. **Provide Evidence**: Add links, documents, or proof of accomplishment (optional)
8. **Add Description**: Include additional details about the accomplishment (optional)

## Files Created

### Core Files
- `accomplishment.php` - Model class for database operations
- `index.php` - Main UI with DataTables and modals for CRUD operations
- `process.php` - Handles Create, Update, Delete operations with CSRF protection
- `get_accomplishments.php` - AJAX endpoint to fetch accomplishments for DataTables
- `get_targets.php` - AJAX endpoint to fetch targets based on selected SDP
- `get_responsibilities.php` - AJAX endpoint to fetch offices based on selected SDP

### Features Implemented

1. **Dynamic Cascading Dropdowns**
   - SDP selection loads associated targets and responsible offices
   - Year and Quarter filters dynamically filter available targets
   - Form fields enable/disable based on selections

2. **CRUD Operations**
   - Create: Add new accomplishments with validation
   - Read: View all accomplishments in a searchable, sortable DataTable
   - Update: Edit existing accomplishments
   - Delete: Remove accomplishments with confirmation

3. **Data Display**
   - Shows SDP (KPI), Office, Target details, Accomplishment value
   - Displays evidence and description
   - Action buttons for edit and delete

4. **Security**
   - Session-based authentication
   - CSRF token protection on all POST requests
   - Input sanitization and validation
   - Activity logging for audit trail

5. **User Experience**
   - Bootstrap 5 modals for forms
   - Toast notifications for success/error messages
   - Loading spinners during AJAX requests
   - Responsive DataTables with search and pagination
   - Form validation and helpful hints

## Database Table Used

The module interacts with:
- `tbl_accomplishment` - Main table for accomplishment records
- `tbl_sdp` - Strategic Development Plans
- `tbl_target` - Targets for SDPs
- `tbl_responsibility_matrix` - Offices/units
- `tbl_sdp_responsibility` - Junction table linking SDPs to offices

## Navigation

The Accomplishments module has been added to the sidebar navigation with a trophy icon.

## Permissions

Currently accessible to all authenticated users. Role-based restrictions can be added in future updates if needed.
