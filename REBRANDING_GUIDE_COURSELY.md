# Complete Rebranding Guide: Rocket LMS → Coursely LMS

**Target Rebrand:** From "Rocket LMS" to "Coursely LMS"  
**Date:** 2025-09-06  
**Scope:** Complete brand transformation including all references, logos, and configurations

---

## Overview

This guide provides step-by-step instructions to completely rebrand the Laravel-based Rocket LMS to "Coursely LMS". The system is well-designed with most branding being dynamic through admin settings, making the rebranding process straightforward.

### Rebranding Impact Summary
- **File Changes Required:** 4 core files
- **Admin Panel Updates:** 6 settings areas
- **Asset Replacements:** 3 logo files minimum
- **Database Updates:** Dynamic content through admin panel
- **Estimated Time:** 2-3 hours for complete rebrand

---

## Phase 1: Core File Modifications

### 1.1 Environment Configuration

**File:** `.env`

```env
# Current → New
APP_NAME=rocketlms → APP_NAME=courselylms
APP_URL=https://lms.rocket-soft.org/ → APP_URL=https://lms.coursely.org/
MAIL_FROM_NAME="Platform Title" → MAIL_FROM_NAME="Coursely LMS"
```

**Impact:** Changes app name throughout the system and email sender name.

### 1.2 Admin Panel Navbar

**File:** `./resources/views/admin/includes/navbar.blade.php`

**Lines to Change:**
```html
<!-- Line 28: Version Display -->
Rocket LMS Version 1.7.2 → Coursely LMS Version 1.7.2

<!-- Line 29: Copyright Notice -->
<div class="time text-primary">All rights reserved for Rocket Soft</div>
→
<div class="time text-primary">All rights reserved for Coursely</div>
```

### 1.3 Admin Login Page

**File:** `./resources/views/admin/auth/auth_layout.blade.php`

**Lines to Change:**
```html
<!-- Line 45: Main Title -->
<h1 class="mb-2 display-4 font-weight-bold">Rocket LMS</h1>
→
<h1 class="mb-2 display-4 font-weight-bold">Coursely LMS</h1>

<!-- Line 48: Footer Link -->
All rights reserved for <a class="text-light bb" target="_blank" href="https://codecanyon.net/user/rocketsoft">Rocket Soft</a>
→
All rights reserved for <a class="text-light bb" target="_blank" href="https://coursely.org">Coursely</a>
```

### 1.4 System Check File

**File:** `./public/check.php`

**Find and Replace:**
```php
Rocket LMS Version : 1.7.2 → Coursely LMS Version : 1.7.2
```

---

## Phase 2: Asset Replacement

### 2.1 Logo Files to Replace

**Required Logo Assets:**
1. `./public/store/1/default_images/website-logo.png`
2. `./public/store/1/default_images/website-logo-white.png`
3. `./public/store/1/default_images/logo-new.jpg`

**Specifications:**
- **Standard Logo:** PNG format, transparent background, 200px height recommended
- **White Logo:** PNG format, white version for dark backgrounds
- **Favicon:** ICO/PNG format, 32x32px minimum

### 2.2 Additional Logo Locations

**Dynamic User-Generated Logos** (may exist):
- `./public/store/929/logo/` (example user store)
- `./public/store/1015/logo_files/` (example user store)

**Note:** These are user-uploaded logos and will be managed through admin panel.

---

## Phase 3: Admin Panel Configuration

### 3.1 General Settings

**Path:** Admin Panel → Settings → General

**Updates Required:**
1. **Site Name:** "Rocket LMS" → "Coursely LMS"
2. **Site Description:** Update to reflect Coursely branding
3. **Site Email:** Update sender email if needed
4. **Contact Information:** Update company details

### 3.2 Appearance Settings

**Path:** Admin Panel → Settings → Appearance

**Updates Required:**
1. **Main Logo:** Upload new Coursely logo
2. **Footer Logo:** Upload footer version if different
3. **Favicon:** Upload Coursely favicon
4. **Admin Panel Logo:** Update admin logo

### 3.3 SEO Settings

**Path:** Admin Panel → Settings → SEO

**Updates Required:**
1. **Site Title:** Include "Coursely LMS"
2. **Meta Description:** Update brand description
3. **Keywords:** Replace brand-related keywords
4. **Open Graph Tags:** Update social sharing information

### 3.4 Email Settings

**Path:** Admin Panel → Settings → Notifications

**Updates Required:**
1. **Email Templates:** Review all email templates for branding
2. **Sender Name:** Ensure consistency with .env changes
3. **Email Signatures:** Update company signature

### 3.5 Legal Pages

**Path:** Admin Panel → Content Management → Pages

**Updates Required:**
1. **Terms of Service:** Replace company name and branding
2. **Privacy Policy:** Update company references
3. **About Us:** Complete rewrite for Coursely
4. **Contact Us:** Update company information

### 3.6 Social Media Links

**Path:** Admin Panel → Settings → Social Media

**Updates Required:**
1. Update all social media URLs to Coursely accounts
2. Remove old Rocket LMS social links
3. Add new social media profiles

---

## Phase 4: Database Content Review

### 4.1 System-Generated Content

**Check These Areas:**
1. **Default Categories:** Review category names for brand references
2. **System Notifications:** Check notification templates
3. **Default Content:** Any sample/demo content
4. **FAQ Sections:** Update company-related questions

### 4.2 User-Generated Content

**Manual Review Required:**
1. **Sample Courses:** Remove or update demo courses
2. **User Comments:** Search for brand mentions in comments
3. **Forum Posts:** Check community discussions
4. **Support Tickets:** Historical ticket content

---

## Phase 5: Technical Implementation

### 5.1 File Modification Commands

**Step 1: Backup Current Files**
```bash
cp .env .env.backup
cp ./resources/views/admin/includes/navbar.blade.php ./resources/views/admin/includes/navbar.blade.php.backup
cp ./resources/views/admin/auth/auth_layout.blade.php ./resources/views/admin/auth/auth_layout.blade.php.backup
cp ./public/check.php ./public/check.php.backup
```

**Step 2: Apply Changes**
Use find and replace in your editor or apply manual changes as outlined above.

**Step 3: Clear System Cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### 5.2 Testing Checklist

**Frontend Testing:**
- [ ] Home page displays new branding
- [ ] Logo appears correctly
- [ ] Email notifications use new brand name
- [ ] Social sharing shows correct information

**Admin Panel Testing:**
- [ ] Admin login page shows new branding
- [ ] Dashboard header displays correctly
- [ ] Version information updated
- [ ] Settings pages reflect changes

**Email Testing:**
- [ ] Send test emails to verify sender name
- [ ] Check email templates for consistency
- [ ] Verify notification branding

---

## Phase 6: Optional Enhancements

### 6.1 Custom Branding Elements

**Color Scheme Updates:**
- Update CSS variables for brand colors
- Modify theme colors in admin panel
- Customize button and accent colors

**Typography:**
- Update font choices to match brand
- Modify heading styles if needed
- Ensure readability with new color scheme

### 6.2 Advanced Customizations

**Custom Icons:**
- Replace FontAwesome rocket icon in sidebar if desired
- Add custom branded icons
- Update loading animations/spinners

**Additional Assets:**
- Custom error page backgrounds
- Branded email headers
- Social media images for sharing

---

## Phase 7: Post-Launch Tasks

### 7.1 SEO Migration

**Search Engine Updates:**
1. Update Google Search Console settings
2. Submit new sitemap with updated branding
3. Monitor search rankings for brand terms
4. Update social media profiles

### 7.2 Marketing Materials

**Update Required:**
1. Business cards and letterheads
2. Marketing brochures and flyers  
3. Website footer links and partnerships
4. Email signatures for staff

### 7.3 Legal Considerations

**Important Notes:**
- Ensure trademark clearance for "Coursely" name
- Update business registration if needed
- Review licensing agreements for affected terms
- Update copyright notices throughout system

---

## Rollback Plan

### Emergency Rollback Procedure

**If issues occur after rebranding:**

1. **Restore Backup Files:**
   ```bash
   cp .env.backup .env
   cp ./resources/views/admin/includes/navbar.blade.php.backup ./resources/views/admin/includes/navbar.blade.php
   cp ./resources/views/admin/auth/auth_layout.blade.php.backup ./resources/views/admin/auth/auth_layout.blade.php
   cp ./public/check.php.backup ./public/check.php
   ```

2. **Revert Admin Settings:**
   - Restore previous logos through admin panel
   - Revert site name and description
   - Restore original email settings

3. **Clear Cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

---

## Quality Assurance Checklist

### Pre-Launch Verification

**Technical Checks:**
- [ ] All 4 core files updated correctly
- [ ] New logos uploaded and displaying
- [ ] Email tests successful with new branding
- [ ] Admin panel fully functional
- [ ] No broken links or missing assets
- [ ] Mobile responsiveness maintained

**Content Checks:**
- [ ] All visible "Rocket LMS" references replaced
- [ ] Copyright notices updated
- [ ] Contact information current
- [ ] Legal pages reviewed and updated
- [ ] Social media links functional

**User Experience:**
- [ ] Registration/login process works
- [ ] Course creation and management functional
- [ ] Payment processing unaffected
- [ ] Email notifications properly branded
- [ ] No user-facing errors or confusion

---

## Timeline Estimate

### Recommended Implementation Schedule

**Day 1 (2-3 hours):**
- File modifications (1 hour)
- Logo preparation and upload (1 hour)  
- Admin panel configuration (1 hour)

**Day 2 (1-2 hours):**
- Content review and updates
- Testing and quality assurance
- SEO and social media updates

**Day 3 (1 hour):**
- Final testing and deployment
- Staff training on new branding
- Marketing material updates

---

## Support and Maintenance

### Ongoing Brand Management

**Monthly Tasks:**
- Monitor for any missed brand references
- Update marketing materials as needed
- Review user-generated content for old branding

**Quarterly Tasks:**
- Comprehensive brand audit
- Update promotional materials
- Review and update legal documents

---

## Conclusion

This comprehensive rebranding guide ensures a complete transformation from "Rocket LMS" to "Coursely LMS". The Laravel-based system's excellent architecture makes the rebranding process manageable with minimal technical risk.

**Key Success Factors:**
1. **Complete File Coverage:** All brand references identified and addressed
2. **Admin Panel Integration:** Leveraging dynamic content management
3. **Quality Assurance:** Thorough testing prevents user disruption
4. **Rollback Plan:** Emergency procedures ensure business continuity

Following this guide will result in a fully rebranded learning management system that maintains all functionality while presenting the new "Coursely LMS" brand consistently across all user touchpoints.

---

**Document Version:** 1.0  
**Last Updated:** 2025-09-06  
**Prepared for:** Coursely LMS Rebranding Project