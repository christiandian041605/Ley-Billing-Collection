# RISENUP SDP Accordion Design

## Changes Applied

### 1. Search Bar
Added a search input at the top to filter SDPs in real-time.

```
┌─────────────────────────────────────────────────────┐
│ 🔍 Search SDPs by KPI, initiatives, or objectives... │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Icon prefix for visual clarity
- Placeholder text guides users
- Real-time filtering as you type
- Searches across KPI, initiatives, and objectives

---

### 2. Accordion Layout
Replaced card grid with accordion for better space management.

**Before:** 2-column card grid
```
┌──────────┐  ┌──────────┐
│ SDP #1   │  │ SDP #2   │
│ Card     │  │ Card     │
└──────────┘  └──────────┘
```

**After:** Single-column accordion
```
▼ KPI Name 1                                    [75%]
  ████████████████░░░░░░░░ 
  [Expanded content...]

▶ KPI Name 2                                    [45%]
  ████████░░░░░░░░░░░░░░░░

▶ KPI Name 3                                    [90%]
  ██████████████████░░░░░░
```

---

### 3. Accordion Header
Each accordion header shows:

**Components:**
- **KPI Title** (bold, main identifier)
- **Progress Badge** (percentage on the right)
- **Progress Bar** (visual progress indicator)

**Layout:**
```
┌─────────────────────────────────────────────────┐
│ ▼ Increase Student Enrollment by 20%      [75%]│
│   ████████████████░░░░░░░░░░░░░░░░              │
└─────────────────────────────────────────────────┘
```

**Progress Colors:**
- 🟢 Green (75-100%): Excellent progress
- 🟡 Yellow (50-74%): Good progress
- 🔵 Blue (25-49%): Moderate progress
- 🔴 Red (0-24%): Needs attention

---

### 4. Progress Calculation

**Formula:**
```
Progress % = (Total Accomplishments / Total Target Value) × 100
```

**Example:**
- Target Value: 100
- Accomplishments: 75
- Progress: 75%

**Capped at 100%** - Doesn't exceed even if over-accomplished

---

### 5. Accordion Body Content

When expanded, shows:

#### A. Initiatives
```
💡 Initiatives
Description of strategic initiatives...
```

#### B. Objective
```
🎯 Objective
Institutional objective details...
```

#### C. Focus Area
```
🎯 Focus Area
[RESEARCH, TECHNOLOGY AND INNOVATION]
```

#### D. Responsibility Matrix
```
👥 Responsibility Matrix
[Office 1] [Office 2] [Office 3]
```

#### E. Targets Table
```
📅 Targets
┌──────┬─────────┬──────────────┬──────┐
│ Year │ Quarter │ Target Value │ Type │
├──────┼─────────┼──────────────┼──────┤
│ 2024 │   Q1    │     25.5     │  %   │
│ 2024 │   Q2    │     30.0     │  %   │
└──────┴─────────┴──────────────┴──────┘
```

#### F. Progress Summary Box
```
┌──────────────────────────────────────┐
│  Total Target  │  Accomplished  │ Progress │
│     100.00     │     75.00      │   75%    │
└──────────────────────────────────────┘
```

Light gray background, centered text, clear metrics.

---

### 6. Search Functionality

**JavaScript Features:**
- Real-time filtering
- Case-insensitive search
- Searches across:
  - KPI text
  - Initiatives text
  - Objectives text
- Hides non-matching items
- Shows matching items

**User Experience:**
1. Type in search box
2. SDPs filter instantly
3. Non-matching accordions hidden
4. Clear search to show all

---

### 7. Removed Elements

✅ Removed:
- Card headers with "SDP #"
- Multi-column grid layout
- Card footers
- Color-coded headers per focus area

❌ Kept (but moved):
- All SDP information
- KPI (now in header)
- All other fields in body

---

## Visual Improvements

### Before
```
┌────────────────────┐  ┌────────────────────┐
│ SDP #1        [×]  │  │ SDP #2        [×]  │
│──────────────────  │  │──────────────────  │
│ KPI:               │  │ KPI:               │
│ Description...     │  │ Description...     │
│                    │  │                    │
│ Initiatives:       │  │ Initiatives:       │
│ Text...            │  │ Text...            │
│                    │  │                    │
│ (lots of content)  │  │ (lots of content)  │
└────────────────────┘  └────────────────────┘
```

### After
```
┌────────────────────────────────────────────────┐
│ 🔍 Search...                                   │
├────────────────────────────────────────────────┤
│ ▼ KPI Name 1                          [75%]   │
│   ████████████████░░░░░░░░                     │
│   ├─ Initiatives: Text...                     │
│   ├─ Objective: Text...                       │
│   ├─ Targets: [Table]                         │
│   └─ Progress: 75.00 / 100.00                 │
├────────────────────────────────────────────────┤
│ ▶ KPI Name 2                          [45%]   │
│   ████████░░░░░░░░░░░░░░░░                     │
├────────────────────────────────────────────────┤
│ ▶ KPI Name 3                          [90%]   │
│   ██████████████████░░░░░░                     │
└────────────────────────────────────────────────┘
```

---

## Benefits

### Space Efficiency
✅ Vertical stacking saves horizontal space
✅ Only one item expanded at a time
✅ Better for long content
✅ Mobile-friendly single column

### Visual Clarity
✅ Progress bars show status at a glance
✅ Color-coded progress (red/blue/yellow/green)
✅ Clean, organized layout
✅ Easy to scan KPIs

### User Experience
✅ Search filters instantly
✅ Accordion groups related info
✅ Click to expand/collapse
✅ First item open by default

### Information Hierarchy
✅ KPI most prominent (header)
✅ Progress visible without expanding
✅ Details available on demand
✅ Summary metrics at bottom

---

## Technical Details

### Accordion Structure
```php
<div class="accordion" id="sdpAccordion">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button" ...>
        <div class="w-100">
          <div>KPI + Badge</div>
          <div class="progress">...</div>
        </div>
      </button>
    </h2>
    <div class="accordion-collapse collapse">
      <div class="accordion-body">
        ... content ...
      </div>
    </div>
  </div>
</div>
```

### Progress Calculation
```php
// Sum all target values
$total_target_value = array_sum(array_column($targets, 'target_value'));

// Query accomplishments
$total_accomplishment = // sum from DB

// Calculate percentage
$progress = min(100, ($accomplishment / $target) * 100);
```

### Search JavaScript
```javascript
searchInput.addEventListener('keyup', function() {
  const searchTerm = this.value.toLowerCase();
  
  sdpItems.forEach(function(item) {
    const searchableText = item.getAttribute('data-kpi') + 
                          item.getAttribute('data-initiatives') +
                          item.getAttribute('data-objectives');
    
    if (searchableText.includes(searchTerm)) {
      item.style.display = '';
    } else {
      item.style.display = 'none';
    }
  });
});
```

---

## Result

A modern, efficient, and user-friendly SDP display system with:
- 🔍 Real-time search
- 📊 Visual progress tracking
- 📁 Organized accordion layout
- 📱 Mobile-responsive design
- 🎨 Clean, professional appearance

Perfect for managing and viewing Strategic Development Plans! 🎯
