# RISENUP Cards with Accordion Inside

## Final Design

Cards remain in 2-column grid layout with accordion INSIDE each card for collapsible sections.

## Layout Structure

```
┌──────────────────┐  ┌──────────────────┐
│ Card 1           │  │ Card 2           │
│ ─────────────    │  │ ─────────────    │
│ KPI Title   [75%]│  │ KPI Title   [45%]│
│ ████████░░░░░░░░ │  │ ████░░░░░░░░░░░░ │
│ Progress info    │  │ Progress info    │
│                  │  │                  │
│ ▶ Initiatives    │  │ ▶ Initiatives    │
│ ▶ Objective      │  │ ▶ Objective      │
│ ▶ Details        │  │ ▶ Details        │
│ ▶ Targets        │  │ ▶ Targets        │
└──────────────────┘  └──────────────────┘
```

## Card Components

### 1. Card Header Section
- **KPI Title** (h5, bold)
- **Progress Badge** (percentage on right)
- **Progress Bar** (colored, 8px height)
- **Progress Text** (accomplished / target)

```
┌────────────────────────────────────────┐
│ Increase Student Enrollment     [75%] │
│ ████████████████░░░░░░░░░░░░░░         │
│ Progress: 75.00 / 100.00               │
└────────────────────────────────────────┘
```

### 2. Accordion Sections (Inside Card)

#### A. Initiatives
```
▶ 💡 Initiatives
  [Click to expand]
  
▼ 💡 Initiatives
  Description of strategic initiatives...
```

#### B. Objective
```
▶ 🎯 Objective
  [Click to expand]
  
▼ 🎯 Objective
  Institutional objective details...
```

#### C. Details
```
▶ ℹ️ Details
  [Click to expand]
  
▼ ℹ️ Details
  Focus Area: [RESEARCH, TECHNOLOGY AND INNOVATION]
  
  Responsibility Matrix:
  [Office 1] [Office 2] [Office 3]
```

#### D. Targets
```
▶ 📅 Targets
  [Click to expand]
  
▼ 📅 Targets
  ┌──────┬─────────┬──────────────┬──────┐
  │ Year │ Quarter │ Target Value │ Type │
  ├──────┼─────────┼──────────────┼──────┤
  │ 2024 │   Q1    │     25.5     │  %   │
  │ 2024 │   Q2    │     30.0     │  %   │
  └──────┴─────────┴──────────────┴──────┘
```

## Features

### Search Bar
```
┌──────────────────────────────────────────┐
│ 🔍 Search SDPs by KPI, initiatives, or...│
└──────────────────────────────────────────┘
```
- Filters cards in real-time
- Hides non-matching cards
- Searches: KPI, initiatives, objectives

### Progress Tracking
- **Automatic Calculation**: Accomplishments / Targets
- **Color-Coded Bars**:
  - 🟢 Green (75-100%)
  - 🟡 Yellow (50-74%)
  - 🔵 Blue (25-49%)
  - 🔴 Red (0-24%)
- **Badge Display**: Shows percentage

### Grid Layout
- **Desktop (lg)**: 2 columns (col-lg-6)
- **Mobile**: 1 column (col-12)
- **Gap**: g-4 spacing
- **Height**: h-100 (equal height cards)

## Accordion Behavior

### Independent Accordions
- Each card has its own accordion
- Sections collapse independently
- Multiple sections can be open
- No auto-collapse between cards

### Collapsed by Default
- All sections start collapsed
- Click to expand any section
- Clean, compact initial view
- Expand only what you need

## Benefits

✅ **2-Column Grid** - Better use of space on desktop
✅ **Compact View** - Sections collapsed by default
✅ **Quick Overview** - KPI and progress visible immediately
✅ **Organized Info** - Logical grouping in sections
✅ **Search Enabled** - Filter cards instantly
✅ **Progress Visible** - Bar and percentage always shown
✅ **Mobile Friendly** - Single column on small screens

## Visual Hierarchy

1. **Search Bar** (top)
2. **Cards in Grid** (2 columns)
   - **Card Header**: KPI + Progress (always visible)
   - **Accordion**:
     - Initiatives (expandable)
     - Objective (expandable)
     - Details (expandable)
     - Targets (expandable)

## Code Structure

```php
<!-- Search Bar -->
<input id="sdpSearch" ... />

<!-- Cards Grid -->
<div class="row g-4">
  <?php foreach ($sdps as $sdp): ?>
    <div class="col-lg-6 col-12 sdp-item">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          
          <!-- KPI + Progress Bar (Always Visible) -->
          <div class="mb-3">
            <h5><?php echo $sdp['kpi']; ?></h5>
            <div class="progress">...</div>
          </div>
          
          <!-- Accordion Inside Card -->
          <div class="accordion">
            <div class="accordion-item">Initiatives</div>
            <div class="accordion-item">Objective</div>
            <div class="accordion-item">Details</div>
            <div class="accordion-item">Targets</div>
          </div>
          
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
```

## Result

A clean, organized card layout with:
- 🔍 Search functionality
- 📊 Progress tracking
- 📁 Collapsible sections
- 🎨 Professional design
- 📱 Responsive layout

Cards remain in grid, accordions inside for organized content! 🎯
