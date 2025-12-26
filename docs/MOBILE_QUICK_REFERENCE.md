````markdown


### View All CSS Files
```bash
ls assets/css/
```

### Test Mobile View
1. Press `F12` (DevTools)
2. Press `Ctrl+Shift+M` (Device Mode)
3. Select device from dropdown

## File Structure

```
assets/css/
  └── staff-mobile.css     # All mobile styles

includes/
  ├── header.php           # Loads mobile CSS + JS
  ├── staff_sidebar.php    # Mobile-ready sidebar
  └── top_navbar.php       # Has toggle button

staff/
  ├── dashboard.php        # ✓ Mobile ready
  ├── attendance.php       # ✓ Mobile ready
  ├── leaves.php           # ✓ Mobile ready
  ├── payslips.php         # ✓ Mobile ready
  └── profile.php          # ✓ Mobile ready
```

## CSS Classes

### Hide on Mobile
```html
<div class="hide-mobile">Desktop only content</div>
```

### Full Width Button on Mobile
```html
<button class="btn btn-primary clock-btn">Clock In</button>
```

### Mobile Toggle Button
```html
<button class="mobile-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>
```

## JavaScript Functions

### Toggle Sidebar
```javascript
toggleSidebar()  // Opens/closes mobile menu
```

### Close Sidebar
```javascript
closeSidebar()   // Always closes menu
```

## Media Queries

```css
/* Mobile */
@media (max-width: 768px) { }

/* Small Mobile */
@media (max-width: 576px) { }

/* Landscape */
@media (max-width: 768px) and (orientation: landscape) { }
```

## Common Breakpoints

| Device | Width | Notes |
|--------|-------|-------|
| iPhone SE | 375px | Small phone |
| iPhone 12 | 390px | Standard phone |
| Galaxy S20 | 412px | Large phone |
| iPad Mini | 768px | Small tablet |
| iPad Pro | 1024px | Large tablet |

## Testing Checklist

- [ ] Menu toggle works
- [ ] Sidebar closes on selection
- [ ] Buttons are tappable (44px+)
- [ ] Forms work properly
- [ ] Tables scroll horizontally
- [ ] No horizontal page scroll
- [ ] Landscape mode works

## Important Variables

```css
--sidebar-width: 250px;      /* Desktop sidebar */
--primary-color: #0d6efd;    /* Brand color */
```

## Responsive Classes (Bootstrap 5)

```html
<div class="d-block d-md-none">Mobile only</div>
<div class="d-none d-md-block">Desktop only</div>
<div class="col-12 col-md-6">Responsive column</div>
```

## Common Issues & Fixes

### Issue: Sidebar Won't Close
**Fix**: Check if overlay exists in DOM
```javascript
document.querySelector('.sidebar-overlay')
```

### Issue: Layout Broken
**Fix**: Verify CSS loaded
```html
<!-- Check in page source -->
<link href="../assets/css/staff-mobile.css" rel="stylesheet">
```

### Issue: Buttons Too Small
**Fix**: Add proper class
```html
<button class="btn btn-lg clock-btn">
```

## Performance Tips

1. Use CSS transforms (not position changes)
2. Minimize JavaScript
3. Avoid layout thrashing
4. Use will-change sparingly
5. Test on real devices

## Accessibility

- Minimum 44x44px touch targets ✓
- 16px font for inputs (no zoom) ✓
- High contrast ratios ✓
- Keyboard accessible ✓
- Screen reader friendly ✓

## Browser DevTools

### Chrome
```
F12                  → Open DevTools
Ctrl+Shift+M        → Toggle Device Mode
Ctrl+Shift+C        → Inspect Element
Ctrl+Shift+I        → Device Frame
```

### Firefox
```
F12                  → Open DevTools
Ctrl+Shift+M        → Responsive Design Mode
Ctrl+Shift+C        → Inspector
```

## Useful URLs

Local Testing:
```
http://localhost/payroll-php/staff/dashboard.php
```

Network Testing (from phone):
```
http://192.168.1.XXX/payroll-php/staff/dashboard.php
```

## Git Commands

```bash
# Check what changed
git status

# View differences
git diff

# Stage mobile CSS
git add assets/css/staff-mobile.css

# Commit changes
git commit -m "Add mobile view for staff section"
```

## Documentation Files

- `MOBILE_VIEW_GUIDE.md` - Complete guide
- `MOBILE_IMPLEMENTATION_SUMMARY.md` - What was done
- `MOBILE_VIEW_DEMO.md` - Visual examples
- `MOBILE_QUICK_REFERENCE.md` - This file

## Support

Questions? Check these resources:
1. Bootstrap 5 Docs: https://getbootstrap.com/docs/5.3/
2. MDN Media Queries: https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries
3. Google Mobile-Friendly Test: https://search.google.com/test/mobile-friendly

---

**Last Updated**: December 26, 2024
**Version**: 1.0.0
**Status**: Production Ready ✓

````
