# Dashboard Module

## Overview
A comprehensive dashboard that provides a visual overview of the SUNN SDP Tracker system using AdminLTE components and Chart.js for data visualization.

## Features

### 1. **Statistics Overview Cards**
Six color-coded info boxes displaying key metrics:
- **Users** (Blue) - Total registered users
- **SDPs** (Green) - Total Strategic Development Plans
- **Accomplishments** (Yellow) - Total accomplishments recorded
- **Objectives** (Red) - Institutional objectives defined
- **Offices/Units** (Cyan) - Total offices in responsibility matrix
- **Targets** (Gray) - Total targets set
- **Completion Rate** (Dark) - Percentage of targets with accomplishments

### 2. **Quarterly Progress Chart**
- **Type**: Bar Chart (Chart.js)
- **Shows**: Comparison of targets vs accomplishments per quarter
- **Data**: Dynamically filtered for the current year
- **Colors**: Blue for targets, Green for accomplishments

### 3. **Top Performing Offices**
- **Display**: Table format
- **Shows**: Top 5 offices ranked by number of accomplishments
- **Badges**: Green badges showing accomplishment count

### 4. **SDPs with Most Targets**
- **Display**: Table format
- **Shows**: Top 5 SDPs ranked by number of targets
- **Badges**: Yellow badges showing target count
- **Text Truncation**: Long KPIs are shortened with ellipsis

### 5. **Recent Activity Feed**
- **Display**: List group
- **Shows**: Last 10 activity log entries
- **Information**: User name, action, module, details, timestamp
- **Link**: Quick access to full activity logs

### 6. **User Role Distribution**
- **Type**: Pie Chart (Chart.js)
- **Shows**: Distribution of users across different roles (Admin, Faculty, Staff, Dean, Director)
- **Colors**: Multi-color palette for easy distinction

### 7. **System Information Panel**
- **Display**: Definition list (dl/dt/dd)
- **Shows**: Comprehensive system statistics
- **Progress Bar**: Visual representation of overall completion rate
- **Status Badge**: System operational status

## Technologies Used

- **PHP**: Server-side data processing
- **AdminLTE**: UI framework and components
- **Bootstrap 5**: Grid system and styling
- **Chart.js 4.4.0**: Data visualization
- **Bootstrap Icons**: Icon set

## Data Sources

All data is fetched from the database tables:
- `tbl_user` - User statistics
- `tbl_sdp` - SDP count
- `tbl_objectives` - Objectives count
- `tbl_accomplishment` - Accomplishments data
- `tbl_responsibility_matrix` - Office information
- `tbl_target` - Target data with quarterly breakdown
- `tbl_activity_logs` - Recent activities

## Color Scheme

Following AdminLTE conventions:
- **Primary (Blue)**: Users, main actions
- **Success (Green)**: SDPs, completions
- **Warning (Yellow)**: Accomplishments, alerts
- **Danger (Red)**: Objectives, critical items
- **Info (Cyan)**: Offices, information
- **Secondary (Gray)**: Targets, supplementary data
- **Dark**: Completion rates, emphasis

## Responsive Design

The dashboard is fully responsive:
- **Desktop**: 3-4 column layout
- **Tablet**: 2 column layout
- **Mobile**: Single column stack

## Quick Links

All statistic cards include "More info" links directing to relevant modules:
- Users → `/users/`
- SDPs → `/sdp/`
- Accomplishments → `/accomplishments/`
- Objectives → `/objectives/`
- Offices → `/responsibility_matrix/`
- Activity → `/activity_log/`

## Performance

- Optimized SQL queries with JOINs
- Limited result sets (TOP 5, LAST 10)
- CDN-based Chart.js loading
- Minimal JavaScript for chart rendering

## Future Enhancements

Potential additions:
- Date range filtering
- Export to PDF/Excel
- Real-time updates with WebSockets
- Drill-down capabilities
- Comparison across multiple years
- Performance trends over time
- Target achievement predictions
