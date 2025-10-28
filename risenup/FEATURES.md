# RISENUP Module Features

## 🎯 Core Concept

Interactive visualization of Strategic Development Plans organized by the **RISENUP** institutional framework.

## 🎨 Visual Design

### Letter Cards Interface
```
┌─────┬─────┬─────┬─────┬─────┬─────┬─────┐
│  R  │  I  │  S  │  E  │  N  │  U  │  P  │
│ 🔵  │ 🟢  │ 🔷  │ 🟡  │ 🔴  │ ⚫  │ ⚫  │
│  5  │  3  │  7  │  4  │  2  │  6  │  8  │
└─────┴─────┴─────┴─────┴─────┴─────┴─────┘
```

### SDP Card Layout
```
╔════════════════════════════════════╗
║ SDP #1                    [Colored]║
╠════════════════════════════════════╣
║ 📊 KPI                             ║
║ Description here...                ║
║────────────────────────────────────║
║ 💡 Initiatives                     ║
║ Details here...                    ║
║────────────────────────────────────║
║ 🎯 Objective                       ║
║ Objective details...               ║
║────────────────────────────────────║
║ 🏢 Responsibility Matrix           ║
║ [Office1] [Office2] [Office3]      ║
║────────────────────────────────────║
║ 📅 Targets                         ║
║ Year | Quarter | Value | Type      ║
║ 2024 |   Q1    | 75.5  | %         ║
╚════════════════════════════════════╝
```

## 📋 Information Display

### Each SDP Card Shows:

1. **Header** (Colored)
   - SDP ID number
   - Icon indicator

2. **KPI Section**
   - Icon: Graph/chart
   - Key Performance Indicator text
   - Full description

3. **Initiatives Section**
   - Icon: Lightbulb
   - Strategic initiatives
   - Action items

4. **Objective Section**
   - Icon: Target
   - Linked objective
   - Institutional goal

5. **Focus Area Badge**
   - Icon: Bullseye
   - Colored badge
   - Focus area name

6. **Responsibility Matrix**
   - Icon: People
   - Multiple office badges
   - Gray/secondary styling

7. **Targets Table**
   - Icon: Calendar
   - Sortable data
   - Year, Quarter, Value, Type

## 🎭 Focus Areas

| Letter | Color     | Full Name                          | Icon      | Theme              |
|--------|-----------|------------------------------------|-----------|--------------------|
| R      | Blue      | Research, Technology & Innovation  | 🔍        | Research           |
| I      | Green     | Internationalization               | 🌍        | Global             |
| S      | Cyan      | Sustainability of Good Governance  | ♻️         | Sustainability     |
| E      | Yellow    | Extension and Linkages             | 🔗        | Community          |
| N      | Red       | Novelty in Practices               | 💡        | Innovation         |
| U      | Gray      | University Ranking & Recognition   | 🏆        | Excellence         |
| P      | Dark      | People                             | 👥        | Human Resources    |

## 🖱️ Interactions

### Click Actions
- **Letter Card** → Filter SDPs by focus area
- **Alert Close** → Dismiss focus area header
- **Breadcrumb** → Navigate back

### Hover Effects
- Letter cards lift up with shadow
- Smooth 0.3s transitions
- Visual feedback on clickable elements

### Active States
- Selected letter has thick colored border (3px)
- Alert banner shows focus area
- Count badges update dynamically

## 📊 Data Presentation

### Welcome Screen (No Selection)
- Large colorful RISENUP title
- Framework description
- 3 feature highlight cards
- Call-to-action message

### Focus Area View (Selection Made)
- Colored alert banner with icon
- Focus area full name
- Dismissible alert
- Filtered SDP cards in grid

### Empty State
- Info alert when no SDPs found
- Helpful message
- Icon indicator

## 🎨 Color Scheme

```
Primary   (Blue)      #0d6efd - Research
Success   (Green)     #198754 - International
Info      (Cyan)      #0dcaf0 - Sustainability
Warning   (Yellow)    #ffc107 - Extension
Danger    (Red)       #dc3545 - Novelty
Secondary (Gray)      #6c757d - University
Dark      (Black)     #212529 - People
```

## 📱 Responsive Behavior

### Desktop (lg)
- 7 letter cards in one row
- 2 SDP cards per row
- Full table display

### Tablet (md)
- Letter cards wrap (2 rows)
- 1-2 SDP cards per row
- Table scrolls horizontally

### Mobile (sm/xs)
- Letter cards in 3 columns
- 1 SDP card per row
- Compact table view

## ✨ Special Features

### Dynamic Counting
- Real-time SDP count per focus area
- Badge updates on data change
- Visual indicator of data volume

### Color Coordination
- Everything matches selected letter color
- Headers, badges, table headers
- Consistent theme throughout

### Information Hierarchy
- Most important at top (KPI)
- Supporting details below
- Targets at bottom (detailed data)

## 🔄 Navigation Flow

```
Home → RISENUP (Welcome)
         ↓
   Click Letter (R/I/S/E/N/U/P)
         ↓
   View Filtered SDPs
         ↓
   Click Another Letter (Switch)
         ↓
   View Different SDPs
```

## 🎯 Use Cases

1. **Strategic Planning** - View SDPs by focus area
2. **Reporting** - Organized by institutional framework
3. **Analysis** - Compare focus area activities
4. **Communication** - Present aligned to RISENUP
5. **Monitoring** - Track progress by category

## 💼 Business Value

- **Alignment** - SDPs tied to institutional framework
- **Clarity** - Easy navigation and filtering
- **Visibility** - All details in one place
- **Accessibility** - Intuitive interface
- **Efficiency** - Quick focus area switching

This module transforms complex SDP data into an intuitive, framework-aligned visualization!
