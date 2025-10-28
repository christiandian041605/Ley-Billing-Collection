# RISENUP UI Design Proposals

## Current Issue
Small-box widgets don't work well for the RISENUP letter navigation.

## Proposed Solutions

### Option 1: Pills/Badges Navigation (RECOMMENDED)
Clean, horizontal pill buttons that look like tabs or tags.

```
┌─────────────────────────────────────────────────────────────────┐
│  [R] Research (5)  [I] International (3)  [S] Sustainability (7)│
│  [E] Extension (4)  [N] Novelty (2)  [U] Ranking (6)  [P] People│
└─────────────────────────────────────────────────────────────────┘
```

**Pros:**
- Clean and compact
- Professional look
- Easy to see all options at once
- Color-coded with badges
- Mobile friendly

---

### Option 2: Nav Pills (Tab-like)
Bootstrap nav pills that look like navigation tabs.

```
┌─────────────────────────────────────────────────────────────────┐
│  [ R • Research ]  [ I • International ]  [ S • Sustainability ]│
│  [ E • Extension ]  [ N • Novelty ]  [ U • Ranking ]  [ P • PE ]│
└─────────────────────────────────────────────────────────────────┘
```

**Pros:**
- Very clean interface
- Clear active state
- Familiar navigation pattern
- Good for filtering

---

### Option 3: Colorful Buttons with Icons
Large, colorful rounded buttons with icons and counts.

```
┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐
│   🔍   │ │   🌍   │ │   ♻️    │ │   🔗   │
│   R    │ │   I    │ │   S    │ │   E    │
│Research│ │Internat│ │Sustain │ │Extension│
│   5    │ │   3    │ │   7    │ │   4    │
└────────┘ └────────┘ └────────┘ └────────┘
```

**Pros:**
- Visual and appealing
- Clear categories
- Good spacing
- Icon representation

---

### Option 4: Horizontal Card Deck
Thin horizontal cards with gradient backgrounds.

```
┌──────────────────────────────────────────────────────────────┐
│ R | Research, Technology & Innovation              | 5 SDPs  │
├──────────────────────────────────────────────────────────────┤
│ I | Internationalization                           | 3 SDPs  │
├──────────────────────────────────────────────────────────────┤
│ S | Sustainability of Good Governance              | 7 SDPs  │
└──────────────────────────────────────────────────────────────┘
```

**Pros:**
- Shows full name
- Detailed information
- Easy to read
- Clean layout

---

### Option 5: Segmented Control (iOS-style)
Segmented button group.

```
┌───────────────────────────────────────────────────────────┐
│ │ R │ I │ S │ E │ N │ U │ P │                            │
└───────────────────────────────────────────────────────────┘
      Research (5 SDPs)
```

**Pros:**
- Very compact
- Modern look
- Single line
- Clear selection

---

### Option 6: Large Icon Buttons (Material Design)
Large circular or square buttons with material design.

```
╭─────╮  ╭─────╮  ╭─────╮  ╭─────╮
│  R  │  │  I  │  │  S  │  │  E  │
│ 🔬  │  │ 🌐  │  │ ⚖️   │  │ 🤝  │
╰─────╯  ╰─────╯  ╰─────╯  ╰─────╯
Research  Internat Sustain Extension
   (5)      (3)      (7)      (4)
```

**Pros:**
- Visual hierarchy
- Modern design
- Icon + text
- Hover effects

---

## Recommendation

**Option 1: Pills/Badges Navigation** is the best choice because:

✅ Clean and professional
✅ Doesn't compete with the SDP cards below
✅ Color-coded for easy identification
✅ Shows counts inline
✅ Mobile responsive
✅ Matches AdminLTE style
✅ Compact - doesn't take much vertical space
✅ Easy to implement

### Implementation:
- Use Bootstrap button groups or nav pills
- Add badges for counts
- Color each pill with the focus area color
- Active state shows filled background
- Inactive state shows outline style
- Hover effect for interactivity

Would you like me to implement Option 1, or would you prefer a different option?
