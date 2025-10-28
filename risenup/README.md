# RISENUP Module

## Overview
An interactive module that organizes Strategic Development Plans (SDPs) by the RISENUP framework - a strategic institutional excellence model.

## RISENUP Framework

The acronym **RISENUP** represents seven focus areas for institutional development:

1. **R** - RESEARCH, TECHNOLOGY AND INNOVATION
2. **I** - INTERNATIONALIZATION
3. **S** - SUSTAINABILITY OF GOOD GOVERNANCE
4. **E** - EXTENSION AND LINKAGES
5. **N** - NOVELTY IN PRACTICES
6. **U** - UNIVERSITY RANKING AND RECOGNITION
7. **P** - PEOPLE

## Features

### 1. Interactive Letter Cards
- 7 clickable letter cards representing each focus area
- Each card displays:
  - Large bold letter with unique color
  - Focus area title
  - Count badge showing number of SDPs
  - Hover effect with shadow
- Active selection highlighted with colored border

### 2. Color-Coded System
- **R** - Blue (Primary)
- **I** - Green (Success)
- **S** - Cyan (Info)
- **E** - Yellow (Warning)
- **N** - Red (Danger)
- **U** - Gray (Secondary)
- **P** - Dark (Dark)

### 3. SDP Display Cards
When a letter is clicked, shows:
- **Card Header** - Colored with SDP ID
- **KPI** - Key Performance Indicator
- **Initiatives** - Strategic initiatives
- **Objective** - Linked objective details
- **Focus Area** - Badge showing the focus area
- **Responsibility Matrix** - Office/unit badges
- **Targets Table** - Year, Quarter, Target Value, Type

### 4. Welcome Screen
Default view when no letter is selected:
- Large colorful RISENUP logo
- Framework description
- 3 feature highlight cards
- Call-to-action message

## User Interface

### Navigation Flow
1. User lands on welcome screen
2. Clicks on any RISENUP letter (R, I, S, E, N, U, P)
3. System filters and displays SDPs for that focus area
4. Each SDP shown in a detailed card with all information
5. User can click another letter to switch focus areas

### Card Layout
- Two-column layout on desktop (lg: 6 cols each)
- Single column on mobile
- Shadow effects for depth
- Color-coordinated headers matching selected letter

### Information Hierarchy
Each SDP card displays information in this order:
1. KPI (most important)
2. Initiatives
3. Objective
4. Focus Area
5. Responsibility Matrix
6. Targets (detailed table)

## Technical Details

### Database Queries
- Fetches SDPs based on `focus_area` from `tbl_objectives`
- Joins multiple tables:
  - `tbl_sdp` - SDP data
  - `tbl_objectives` - Objectives and focus areas
  - `tbl_sdp_responsibility` - SDP-office relationships
  - `tbl_responsibility_matrix` - Office details
  - `tbl_target` - Target data

### URL Parameter
- `?focus=R` - Shows Research SDPs
- `?focus=I` - Shows Internationalization SDPs
- `?focus=S` - Shows Sustainability SDPs
- `?focus=E` - Shows Extension SDPs
- `?focus=N` - Shows Novelty SDPs
- `?focus=U` - Shows University Ranking SDPs
- `?focus=P` - Shows People SDPs

### Responsive Design
- Bootstrap 5 grid system
- Letter cards: 1 col (lg), 2 cols (md), 3 cols (mobile)
- SDP cards: 2 cols (lg), 1 col (mobile)
- Mobile-first approach

## Visual Elements

### Icons Used
- **R** - `bi-search` (Research icon)
- **I** - `bi-globe` (International icon)
- **S** - `bi-recycle` (Sustainability icon)
- **E** - `bi-link-45deg` (Extension/linkage icon)
- **N** - `bi-lightbulb` (Innovation icon)
- **U** - `bi-trophy` (Achievement icon)
- **P** - `bi-people` (People icon)

### Animations
- Hover effect on letter cards (lift up + shadow)
- Smooth transitions (0.3s ease)
- Alert dismissible for focus area header

### Typography
- Letter cards: 2.5rem font size, weight 900
- Headers: h5, h6 with color coding
- Body text: Default Bootstrap sizing

## Data Display

### Targets Table
- Responsive table with horizontal scroll on mobile
- Color-coded header matching focus area
- Columns: Year, Quarter, Target Value, Type
- Badge for value type (Percentage, Count, Index, Other)

### Badges
- Focus area badge (colored)
- Office badges (gray/secondary)
- Count badges on letter cards
- Value type badges in tables

## User Experience

### Empty States
- No SDPs message when focus area has no data
- Welcome screen when no selection made
- Graceful handling of missing data (shows "N/A")

### Feedback
- Active letter highlighted with thick border
- Alert header shows selected focus area
- Dismissible alert for clean interface
- Count badges show data availability

## File Structure
```
/risenup/
├── index.php          # Main module file
└── README.md          # This file
```

## Future Enhancements

Potential additions:
- Export SDPs by focus area to PDF
- Print-friendly view
- Search/filter within focus area
- Progress indicators per focus area
- Comparison between focus areas
- Timeline view of targets
- Accomplishment integration per focus area

## Integration

### Sidebar Navigation
Added to main navigation with flag icon:
- Icon: `bi-flag-fill`
- Label: "RISENUP"
- Position: After SDP, before Accomplishments

### Database Dependencies
Requires data in:
- `tbl_sdp` - Strategic Development Plans
- `tbl_objectives` - Must have `focus_area` matching RISENUP categories
- `tbl_sdp_responsibility` - Office assignments
- `tbl_responsibility_matrix` - Office details
- `tbl_target` - Target data

## Usage

1. Navigate to RISENUP from sidebar
2. View welcome screen with framework explanation
3. Click any letter (R, I, S, E, N, U, P)
4. View all SDPs for that focus area
5. Scroll through detailed SDP cards
6. Click another letter to switch focus areas
7. Click on alert close button to dismiss header

This module provides an intuitive, visual way to explore SDPs organized by the institutional strategic framework!
