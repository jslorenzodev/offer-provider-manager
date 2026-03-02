# Offer Provider Manager - WordPress Plugin

**Version:** 1.0.0  
**Requires:** WordPress 5.8+, PHP 7.4+  
**No third-party plugins required**

---

## Table of Contents

1. [Installation](#installation)
2. [Initial Setup](#initial-setup)
3. [Step-by-Step Usage](#step-by-step-usage)
   - [Step 1: Create Provider Categories](#step-1-create-provider-categories)
   - [Step 2: Add Offer Providers](#step-2-add-offer-providers)
   - [Step 3: Create Companies](#step-3-create-companies)
   - [Step 4: Share Registration Links](#step-4-share-registration-links)
   - [Step 5: User Registration](#step-5-user-registration)
   - [Step 6: User Login & Landing Page](#step-6-user-login--landing-page)
4. [Security Features](#security-features)
5. [Admin Reference](#admin-reference)
6. [File Structure](#file-structure)
7. [Database Tables](#database-tables)
8. [Troubleshooting](#troubleshooting)

---

## Installation

1. Download the plugin zip file.
2. In your WordPress admin go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**.
4. Click **Activate Plugin**.
5. Go to **Settings → Permalinks** and click **Save Changes**.
   > ⚠️ This step is required. It flushes the rewrite rules so the registration URL works correctly.

---

## Initial Setup

After activation you will see two new items in your WordPress admin sidebar:

- **Offer Providers** — manage providers, categories, and coupons
- **Companies** — manage companies and their category access

Complete the steps below in order before sharing anything with users.

---

## Step-by-Step Usage

### Step 1: Create Provider Categories

Categories control which Offer Providers each company's users can see.

1. Go to **Offer Providers → Provider Categories**
2. Enter a category name (e.g. `Technology`, `Retail`, `Healthcare`)
3. Click **Add New Provider Category**
4. Repeat for all categories you need

> ℹ️ You must create categories before adding providers or companies — both depend on them.

---

### Step 2: Add Offer Providers

Each Offer Provider represents a business offering discounts to your users.

1. Go to **Offer Providers → Add New**
2. Fill in the following:

| Field           | Where                                   | Notes                                              |
| --------------- | --------------------------------------- | -------------------------------------------------- |
| **Name**        | Post title at the top                   | The provider's business name                       |
| **Logo**        | Featured Image (right sidebar)          | Displayed as the provider logo on the listing page |
| **Website URL** | Provider Details meta box               | Full URL including `https://`                      |
| **Categories**  | Provider Categories box (right sidebar) | Assign one or more categories                      |
| **Coupons**     | Coupons meta box                        | Up to 3 coupons per provider (see below)           |

**Adding coupons:**

- Each coupon has two fields:
  - **Discount Amount** — e.g. `20%` or `$10 off`
  - **Discount Code** — e.g. `SAVE20`
- Leave a coupon slot empty to hide it
- Users can click a coupon code on the site to copy it to clipboard

3. Click **Publish** when done

---

### Step 3: Create Companies

Companies group your users together and define which provider categories they can access.

1. Go to **Companies → Add Company**
2. Enter the **Company Name**
3. Check the **Provider Categories** this company's users are allowed to see
4. Click **Save Company**

A unique **Registration Link** is automatically generated for each company. You do not need to create or type it — it is ready immediately after saving.

---

### Step 4: Share Registration Links

1. Go to **Companies** (the list page)
2. Find the company row
3. Click inside the **Registration Link** field — it will auto-select
4. Copy the link and send it to the employees of that company

**Example link:**

```
https://yoursite.com/register/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6/
```

> ⚠️ Each link is unique per company. Do not share one company's link with another company's employees — they will be assigned to the wrong company.

> ℹ️ Links do not expire. If you want to invalidate a link, delete the company and create a new one.

---

### Step 5: User Registration

When a user opens their company's registration link they will see a registration form with:

- The company name displayed at the top
- Fields for: Username, Email, Password, Confirm Password
- A live password strength meter with requirements checklist

**Password requirements (enforced):**

- At least 8 characters
- At least one uppercase letter (A–Z)
- At least one lowercase letter (a–z)
- At least one number (0–9)
- At least one special character (`!@#$%^&*` etc.)

After successful registration:

- The user's account is created as a WordPress **Subscriber**
- They are automatically linked to the company
- A **welcome email** is sent to their email address with their login link
- A success message is shown with a link to log in

---

### Step 6: User Login & Landing Page

1. User goes to your WordPress login page (e.g. `yoursite.com/wp-login.php`)
2. They enter their username and password
3. After login, **non-admin users are automatically redirected** to:
   ```
   yoursite.com/offer-providers/
   ```
4. The listing page shows **only the Offer Providers** that belong to the categories assigned to the user's company
5. Providers from other categories are completely hidden

> ℹ️ Administrators are not filtered or redirected — they see all providers and go to the normal dashboard after login.

---

## Security Features

The registration system includes the following protections:

| Feature                           | Description                                                           |
| --------------------------------- | --------------------------------------------------------------------- |
| **Rate Limiting**                 | After 5 failed attempts, the IP is locked out for 15 minutes          |
| **Nonce Verification**            | Every form submission is verified with a WordPress nonce              |
| **Honeypot Field**                | A hidden field traps bots — if filled, registration is silently faked |
| **Token Format Validation**       | Token in URL is validated with regex before any DB query              |
| **Password Strength Enforcement** | Weak passwords are rejected server-side                               |
| **Input Sanitization**            | All inputs are sanitized before processing or storing                 |
| **Duplicate Checks**              | Username and email are checked for existing accounts                  |
| **Logged-in Redirect**            | Logged-in users visiting the registration URL are redirected away     |
| **noindex meta tag**              | Registration pages are hidden from search engines                     |
| **Generic Error Messages**        | Invalid tokens show a generic message — not whether the token exists  |

---

## Admin Reference

### Companies List Page

```
WP Admin → Companies
```

- Shows all companies with their registration links and assigned categories
- Click **Edit** to change name or categories
- Click **Delete** to remove a company (also removes user and category links)

### Add / Edit Company Page

```
WP Admin → Companies → Add Company
WP Admin → Companies → [Edit]
```

- Set company name
- Check/uncheck Provider Categories for access control

### Offer Providers List

```
WP Admin → Offer Providers → All Offer Providers
```

- Standard WordPress post list
- Shows title, categories, and publication status

### Provider Categories

```
WP Admin → Offer Providers → Provider Categories
```

- Standard WordPress taxonomy management
- Add, edit, and delete categories here

---

## File Structure

```
offer-provider-plugin/
│
├── offer-provider.php          ← Main plugin file, activation hooks, DB setup
│
├── includes/
│   ├── post-types.php          ← Registers 'offer_provider' post type
│   ├── taxonomies.php          ← Registers 'provider_category' taxonomy
│   ├── companies.php           ← Company helper functions
│   ├── registration.php        ← Registration URL, form processing, security
│   ├── admin.php               ← Companies admin panel + provider meta boxes
│   ├── frontend.php            ← Filters provider listings per user/company
│   └── coupons.php             ← Coupons meta box and helpers
│
├── templates/
│   ├── registration.php        ← Standalone registration form page
│   ├── archive-offer-provider.php  ← Providers listing page
│   └── single-offer-provider.php   ← Single provider page
│
├── assets/
│   └── frontend.css            ← Frontend styles
│
└── README.md                   ← This file
```

---

## Database Tables

The plugin creates three custom tables on activation:

### `wp_opm_companies`

Stores company records.

| Column         | Type         | Description                       |
| -------------- | ------------ | --------------------------------- |
| `id`           | BIGINT       | Primary key                       |
| `name`         | VARCHAR(255) | Company name                      |
| `unique_token` | VARCHAR(64)  | Auto-generated registration token |
| `created_at`   | DATETIME     | Creation timestamp                |

### `wp_opm_company_categories`

Links companies to their allowed Provider Categories.

| Column       | Type   | Description                           |
| ------------ | ------ | ------------------------------------- |
| `id`         | BIGINT | Primary key                           |
| `company_id` | BIGINT | References `wp_opm_companies.id`      |
| `term_id`    | BIGINT | WordPress term ID (Provider Category) |

### `wp_opm_user_companies`

Links WordPress users to their company.

| Column       | Type   | Description                      |
| ------------ | ------ | -------------------------------- |
| `id`         | BIGINT | Primary key                      |
| `user_id`    | BIGINT | WordPress user ID                |
| `company_id` | BIGINT | References `wp_opm_companies.id` |

---

## Troubleshooting

### Registration link returns 404

1. Go to **Settings → Permalinks** and click **Save Changes**
2. Deactivate the plugin, then reactivate it
3. Go to **Settings → Permalinks** and click **Save Changes** again
4. Make sure Permalinks is **not** set to "Plain" — use "Post name" or similar
5. Use the full link copied from the Companies list page, not just `/register/`

### Users see no providers after login

- Check the user is assigned to a company: **Companies** list → verify the company exists
- Check the company has categories assigned: **Companies → Edit** → verify categories are checked
- Check the providers are assigned to those same categories: **Offer Providers → Edit** → check categories
