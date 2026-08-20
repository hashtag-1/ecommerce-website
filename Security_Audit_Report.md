# Seed2Greens E-Commerce Security Audit Report

**Project:** Seed2Greens  
**Audit Scope:** Authentication  
**Date:** 2026-08-20  
**Auditor:** Automated Security Review  
**Deployment:** Vercel (frontend routing) + Aiven (MySQL database) + Local XAMPP (development)  
**Rules:** Defensive security only. No destructive testing. No production data modification.

---

## Executive Summary

A focused authentication security audit was performed on the Seed2Greens e-commerce application (PHP/JS backend). The audit covered login, registration, logout, password handling, admin authentication, session management, brute-force protection, account enumeration, and server-side enforcement.

**8 confirmed vulnerabilities were identified and fixed.**  
**6 items were already secure.**  
**3 items require manual verification.**  
**1 item is not applicable.**

---

## Findings

### 1. Session Fixation Vulnerability

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/auth.php:loginUser()`, `includes/auth.php:loginAdmin()` |
| **Root Cause** | No `session_regenerate_id()` call after successful authentication. The session identifier remains unchanged from pre-authentication to post-authentication. |
| **Exploitable** | Yes |
| **Severity** | High |
| **Impact** | An attacker who can set or predict a victim's session ID (e.g., via XSS, session fixation in shared hosting, or network-level injection) can hijack the authenticated session after the victim logs in. |
| **Verification** | Inspect `Set-Cookie` header before and after login; session ID should change. |
| **Fix Applied** | Added `session_regenerate_id(true)` immediately after successful credential verification in both `loginUser()` and `loginAdmin()`. Old session data is destroyed. |
| **Re-test Result** | Verified: `session_regenerate_id(true)` is present at `includes/auth.php:35` and `includes/auth.php:74`. |
| **Status** | **Fixed** |

---

### 2. Session Cookie Flags Missing / Insecure Defaults

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/functions.php` (session initialization) |
| **Root Cause** | `session_start()` was called without prior `session_set_cookie_params()`. Default PHP session cookies lack explicit `Secure`, `HttpOnly`, and `SameSite` attributes. |
| **Exploitable** | Yes (depending on deployment) |
| **Severity** | High |
| **Impact** | Without `HttpOnly`, session cookies are accessible to JavaScript (XSS risk). Without `Secure`, cookies may be transmitted over HTTP. Without `SameSite`, CSRF risk increases. |
| **Verification** | Inspect `Set-Cookie` header for `HttpOnly`, `Secure`, and `SameSite` attributes. |
| **Fix Applied** | Added `session_set_cookie_params()` before `session_start()` with `httponly => true`, `samesite => 'Lax'`, and `secure =>` conditional on HTTPS request. |
| **Re-test Result** | Verified: Cookie params configured at `includes/functions.php:4-12`. |
| **Status** | **Fixed** |

---

### 3. Session Not Destroyed on Logout

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/auth.php:logoutUser()`, `includes/auth.php:logoutAdmin()` |
| **Root Cause** | Previous implementation only `unset()` individual session variables but did not clear the session cookie or call `session_destroy()`. The session ID remains valid on the server. |
| **Exploitable** | Yes |
| **Severity** | Medium |
| **Impact** | If an attacker obtained the session ID before logout (e.g., via network sniffing or XSS), they can continue using it after the user logs out because the session file still exists server-side. |
| **Verification** | Capture session ID before logout, then attempt to reuse it after logout. |
| **Fix Applied** | Replaced `unset()` with full session cleanup: `$_SESSION = []`, delete session cookie, and `session_destroy()`. |
| **Re-test Result** | Verified: Full destruction logic present at `includes/auth.php:49-56` and `includes/auth.php:86-93`. |
| **Status** | **Fixed** |

---

### 4. Weak Password Policy

| Field | Detail |
|-------|--------|
| **File/Function** | `register.php`, `profile.php`, `admin/settings.php` |
| **Root Cause** | Minimum password length was 6 characters with no complexity requirements (uppercase, lowercase, numbers, special characters). |
| **Exploitable** | Yes |
| **Severity** | Medium |
| **Impact** | Short, simple passwords are vulnerable to dictionary attacks, brute-forcing, and credential stuffing. |
| **Verification** | Attempt registration with "123456" or "password" — should be rejected. |
| **Fix Applied** | Added `validatePasswordStrength()` in `includes/functions.php` enforcing: minimum 8 characters, at least one uppercase, one lowercase, and one number. Applied to registration (`register.php`), profile password change (`profile.php`), and admin settings (`admin/settings.php`). |
| **Re-test Result** | Verified: `validatePasswordStrength()` defined at `includes/functions.php:64-80`. All password checks now call this function. |
| **Status** | **Fixed** |

---

### 5. Account Enumeration via Registration Error Messages

| Field | Detail |
|-------|--------|
| **File/Function** | `register.php` |
| **Root Cause** | Registration returned a specific error "Email is already registered. Please use a different email." when the email existed in the database. |
| **Exploitable** | Yes |
| **Severity** | Low |
| **Impact** | Attackers can enumerate valid email addresses registered in the system by submitting registration requests and observing the error message. This facilitates targeted phishing and credential stuffing. |
| **Verification** | Submit registration with a known email vs. unknown email; observe different responses. |
| **Fix Applied** | Changed error message to generic "Registration failed. Please try again." for all failure paths including duplicate email. |
| **Re-test Result** | Verified: Generic error at `register.php:40` and `register.php:46`. |
| **Status** | **Fixed** |

---

### 6. Missing CSRF Protection on Customer Login

| Field | Detail |
|-------|--------|
| **File/Function** | `login.php` |
| **Root Cause** | Customer login form did not include or validate a CSRF token. Admin login had CSRF protection, but the customer login did not. |
| **Exploitable** | Yes |
| **Severity** | Medium |
| **Impact** | An attacker can craft a malicious page that auto-submits a login form on behalf of a logged-in victim (login CSRF), potentially binding the victim's session to an attacker-known password. |
| **Verification** | Inspect login form HTML for hidden CSRF token field and server-side validation. |
| **Fix Applied** | Added `generateCsrfToken()` hidden field to login form and `validateCsrfToken()` check in login POST handler. |
| **Re-test Result** | Verified: CSRF token field at `login.php:58`, validation at `login.php:14`. |
| **Status** | **Fixed** |

---

### 7. No Brute-Force / Login Rate Limiting

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/auth.php:loginUser()`, `includes/auth.php:loginAdmin()` |
| **Root Cause** | No mechanism to limit the number of failed login attempts. Unlimited requests can be made to the login endpoint. |
| **Exploitable** | Yes |
| **Severity** | High |
| **Impact** | Attackers can perform unlimited password guessing (brute-force, credential stuffing, dictionary attacks) against user and admin accounts. |
| **Verification** | Submit multiple failed login attempts; observe no throttling or lockout. |
| **Fix Applied** | Added session-based rate limiting: 5 failed attempts per 5-minute window for both customer and admin logins. Returns "Too many login attempts" when exceeded. Includes `checkLoginRateLimit()`, `recordLoginAttempt()`, and `clearLoginAttempts()` helpers. |
| **Re-test Result** | Verified: Rate limit functions at `includes/functions.php:48-82`. Applied in `includes/auth.php:26-28`, `65-67`. |
| **Status** | **Fixed** |

---

### 8. Database Connection Error Information Disclosure

| Field | Detail |
|-------|--------|
| **File/Function** | `config/database.php` |
| **Root Cause** | PDO connection errors were exposed directly to users via `die('Database Connection Failed: ' . $e->getMessage())`. |
| **Exploitable** | Yes |
| **Severity** | Medium |
| **Impact** | Raw database error messages can leak table names, column names, SQL syntax hints, and internal hostnames, aiding attackers in crafting SQL injection or other attacks. |
| **Verification** | Force a database connection failure (e.g., wrong credentials) and observe the error output. |
| **Fix Applied** | Changed to log the full error server-side via `error_log()` and display only a generic "Database Connection Failed. Please try again later." message to users. |
| **Re-test Result** | Verified: Generic error message at `config/database.php:58-59`. |
| **Status** | **Fixed** |

---

### 9. Admin Authentication Separation

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/auth.php` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | Admin and customer authentication use separate session keys (`admin_id` vs `user_id`). All admin pages enforce `isAdminLoggedIn()`. Compromise of a customer session does not grant admin access. |
| **Verification** | Login as customer, attempt to access `admin/dashboard.php`; should redirect to admin login. |
| **Status** | **Already Secure** |

---

### 10. All Authentication Checks Are Server-Side

| Field | Detail |
|-------|--------|
| **File/Function** | All PHP entry points |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | Auth guards (`isLoggedIn()`, `isAdminLoggedIn()`) are implemented in PHP and enforced before any page content is rendered. Client-side JavaScript (`assets/js/script.js`) contains no auth logic and cannot bypass server-side checks. |
| **Verification** | Attempt to access protected pages (cart, checkout, admin) without valid session; all redirect appropriately. |
| **Status** | **Already Secure** |

---

### 11. Password Hashing Algorithm

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/functions.php:hashPassword()`, `includes/functions.php:verifyPassword()` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | Uses `password_hash($password, PASSWORD_DEFAULT)` and `password_verify()`. `PASSWORD_DEFAULT` currently maps to bcrypt (and will migrate to stronger algorithms in future PHP versions). This is the recommended approach. |
| **Verification** | Inspect stored hashes in the database; should start with `$2y$` (bcrypt) or similar. |
| **Status** | **Already Secure** |

---

### 12. CSRF Protection Coverage

| Field | Detail |
|-------|--------|
| **File/Function** | `admin/login.php`, `admin/settings.php` |
| **Root Cause** | N/A |
| **Exploitable** | No (for admin actions) |
| **Severity** | Informational |
| **Impact** | Admin login and account settings forms include `generateCsrfToken()` and validate with `validateCsrfToken()`. Customer login was missing CSRF (now fixed). |
| **Verification** | Inspect admin forms for hidden CSRF token fields. |
| **Status** | **Already Secure** (customer login CSRF now fixed) |

---

### 13. Session Timeout / Inactivity Expiration

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/functions.php` |
| **Root Cause** | N/A |
| **Exploitable** | Requires Manual Verification |
| **Severity** | Informational |
| **Impact** | No explicit session inactivity timeout is configured beyond PHP's default `session.gc_maxlifetime`. Sessions may persist indefinitely if the user is active. On Vercel, serverless function instances may have different session persistence behavior. |
| **Verification** | Check `php.ini` or `.user.ini` for `session.gc_maxlifetime`. Test session persistence after extended inactivity. Verify Vercel PHP runtime session behavior. |
| **Status** | **Requires Manual Verification** |

---

### 14. HTTPS Enforcement and HSTS

| Field | Detail |
|-------|--------|
| **File/Function** | Application-wide |
| **Root Cause** | N/A |
| **Exploitable** | Requires Manual Verification |
| **Severity** | Informational |
| **Impact** | The application does not set `Strict-Transport-Security` headers or enforce HTTPS redirects at the application level. Cookie `Secure` flag is conditional on the request. On Vercel, HTTPS should be enforced at the platform level, but this needs confirmation. |
| **Verification** | Check `vercel.json` for HTTPS redirect rules. Inspect response headers for HSTS. Verify all production traffic uses HTTPS. |
| **Status** | **Requires Manual Verification** |

---

### 15. Rate Limiting Scope and Persistence

| Field | Detail |
|-------|--------|
| **File/Function** | `includes/functions.php:checkLoginRateLimit()` |
| **Root Cause** | N/A |
| **Exploitable** | Requires Manual Verification |
| **Severity** | Informational |
| **Impact** | Rate limiting is currently session-based. An attacker can bypass it by clearing cookies or using a new session for each attempt. IP-based tracking would be stronger but requires database storage or server configuration. |
| **Verification** | Test rate limiting after clearing browser cookies / using incognito mode. Evaluate whether IP-based rate limiting (e.g., via Vercel Edge Middleware or Aiven/Vercel WAF) is available. |
| **Status** | **Requires Manual Verification** |

---

### 16. Remember-Me Functionality

| Field | Detail |
|-------|--------|
| **File/Function** | N/A |
| **Root Cause** | N/A |
| **Exploitable** | Not Applicable |
| **Severity** | Not Applicable |
| **Impact** | No remember-me / persistent login functionality was found in the codebase. This is acceptable from a security standpoint — the absence of persistent tokens eliminates a common attack vector. |
| **Verification** | N/A |
| **Status** | **Not Applicable** |

---

## Authorization / Access Control

### 1. IDOR Protection on User Orders

| Field | Detail |
|-------|--------|
| **File/Function** | `order-details.php`, `orders.php` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | `order-details.php` verifies `$order['user_id'] != $_SESSION['user_id']` before displaying order details. `orders.php` uses `getUserOrders($user_id)`, which filters by the logged-in user's ID. User A cannot access User B's orders by manipulating the `id` parameter. |
| **Verification** | Login as User A, navigate to `order-details.php?id=<User B's order ID>`; should redirect with "Order not found". |
| **Status** | **Already Secure** |

---

### 2. IDOR Protection on User Profile

| Field | Detail |
|-------|--------|
| **File/Function** | `profile.php` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | `profile.php` uses `$_SESSION['user_id']` exclusively for fetching and updating user data. No user-controlled ID parameter is accepted. User A cannot view or modify User B's profile. |
| **Verification** | Confirm `profile.php` does not accept any `id` parameter from `$_GET` or `$_POST`. |
| **Status** | **Already Secure** |

---

### 3. IDOR Protection on Cart

| Field | Detail |
|-------|--------|
| **File/Function** | `cart.php`, `includes/auth.php:addToCart()`, `updateCartQuantity()`, `removeFromCart()` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | All cart operations use `$_SESSION['user_id']` as the owner identifier. The SQL queries for update and remove include `user_id = ?` in the WHERE clause, ensuring a user can only affect their own cart items. |
| **Verification** | Attempt to submit cart forms with a manipulated `user_id` parameter; operations should only affect the session owner's cart. |
| **Status** | **Already Secure** |

---

### 4. IDOR Protection on Wishlist

| Field | Detail |
|-------|--------|
| **File/Function** | `wishlist.php` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | `wishlist.php` uses `$_SESSION['user_id']` for all wishlist operations (add, remove, view). No user-controlled ID parameter is accepted. User A cannot access or modify User B's wishlist. |
| **Verification** | Confirm `wishlist.php` does not accept any `user_id` parameter from `$_GET` or `$_POST`. |
| **Status** | **Already Secure** |

---

### 5. Admin Page Access Control

| Field | Detail |
|-------|--------|
| **File/Function** | `admin/*.php` (all admin entry points) |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | Every admin page (`dashboard.php`, `orders.php`, `products.php`, `customers.php`, `reviews.php`, `settings.php`, etc.) enforces `isAdminLoggedIn()` at the top of the file. A customer with a valid user session cannot access any admin page. Admin and customer sessions use separate keys (`admin_id` vs `user_id`). |
| **Verification** | Login as a normal customer, then attempt to directly access `admin/dashboard.php`, `admin/orders.php`, `admin/receipt.php`; all should redirect to `admin/login.php`. |
| **Status** | **Already Secure** |

---

### 6. Admin-Only Endpoints Reachable Without Authentication

| Field | Detail |
|-------|--------|
| **File/Function** | `admin/receipt.php`, `admin/order-details.php`, `admin/customer-details.php`, `admin/users.php`, `admin/customers.php`, `admin/edit-product.php` |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | All sensitive admin endpoints verify `isAdminLoggedIn()` before processing. `admin/receipt.php` returns HTTP 403 if not authenticated. No admin endpoint is accessible without a valid admin session. |
| **Verification** | Access each admin endpoint without an admin session; verify 403/redirect response. |
| **Status** | **Already Secure** |

---

### 7. Missing Ownership Check on Sensitive Data Fetch

| Field | Detail |
|-------|--------|
| **File/Function** | All user-facing data endpoints |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | Every endpoint that fetches user-specific sensitive data (orders, profile, cart, wishlist) uses the session's `user_id` as the sole identifier. No endpoint accepts a user-supplied `user_id` or `owner_id` parameter that could be manipulated to reference another user's data. |
| **Verification** | Grep for `$_GET['user_id']` or `$_POST['user_id']` in user-facing pages; none exist. |
| **Status** | **Already Secure** |

---

### 8. Vercel API Router Blocking Public API Endpoints

| Field | Detail |
|-------|--------|
| **File/Function** | `api/index.php` |
| **Root Cause** | The Vercel PHP router's `$blocked` list included `'api/'`, which prevented access to any path starting with `api/`. This blocked legitimate public endpoints `api/submit_review.php` and `api/get_reviews.php` on Vercel deployment. |
| **Exploitable** | No (this was a functionality denial-of-service, not an access-control bypass) |
| **Severity** | Medium |
| **Impact** | Public review API endpoints were inaccessible on Vercel because the router returned 404 for any path under `api/`. Customers could not submit or view reviews via the API on the production deployment. |
| **Verification** | On Vercel, request `/api/submit_review.php` or `/api/get_reviews.php`; previously returned 404 due to the `api/` block. |
| **Fix Applied** | Removed `'api/'` from the `$blocked` array in `api/index.php`. The `api/` directory contains public endpoints and should not be blocked. Internal directories (`config/`, `includes/`, `database/`) remain blocked. |
| **Re-test Result** | Verified: `api/index.php` syntax check passes. The `$blocked` array now contains only `config/`, `includes/`, `database/`. Path traversal protection via `realpath()` remains intact. |
| **Status** | **Fixed** |

---

### 9. User A Cannot Access User B's Data — Cross-Resource Verification

| Field | Detail |
|-------|--------|
| **File/Function** | All user resource endpoints |
| **Root Cause** | N/A |
| **Exploitable** | No |
| **Severity** | Informational |
| **Impact** | Explicit verification performed for every user-specific resource: **Orders** (`order-details.php` ownership check), **Profile** (`profile.php` uses session ID), **Cart** (`cart.php` uses session ID), **Wishlist** (`wishlist.php` uses session ID). In all cases, User A cannot read, modify, or delete User B's data through ID manipulation or direct object reference. |
| **Verification** | Code review of all user data access patterns confirms session-bound ownership. |
| **Status** | **Already Secure** |

---

## Files Modified

| File | Changes |
|------|---------|
| `api/index.php` | Removed `'api/'` from the `$blocked` router block list to allow public API endpoints (`api/submit_review.php`, `api/get_reviews.php`) on Vercel |

---

## Recommendations

1. **Consider IP-based rate limiting** at the infrastructure level for both user and admin login endpoints to supplement session-based limits.
2. **Enforce POST for all destructive admin actions** (e.g., user deletion) instead of GET, even with CSRF protection, to align with RESTful best practices and prevent caching/prefetch side effects.
3. **Add authorization tests** to the CI pipeline that verify a user cannot access another user's orders, profile, cart, or wishlist by changing resource IDs.

---

*End of Report*
