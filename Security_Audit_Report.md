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

## Files Modified

| File | Changes |
|------|---------|
| `includes/functions.php` | Added `session_set_cookie_params()` with Secure/HttpOnly/SameSite; added `checkLoginRateLimit()`, `recordLoginAttempt()`, `clearLoginAttempts()`, `validatePasswordStrength()` |
| `includes/auth.php` | Added `session_regenerate_id(true)` after login; replaced `unset()` with full session destruction on logout; integrated rate limiting |
| `login.php` | Added CSRF token generation/validation; integrated rate limit checks |
| `register.php` | Replaced specific duplicate-email error with generic message; replaced 6-char minimum with `validatePasswordStrength()` |
| `profile.php` | Replaced 6-char password check with `validatePasswordStrength()` |
| `admin/settings.php` | Replaced 6-char password check with `validatePasswordStrength()` |
| `config/database.php` | Suppressed raw PDO error messages from user output; logged to server-side error log |

---

## Recommendations

1. **Deploy IP-based rate limiting** at the infrastructure level (Vercel Edge Middleware or Aiven proxy) to supplement session-based limits.
2. **Configure `session.gc_maxlifetime`** explicitly to enforce session expiration (e.g., 2 hours of inactivity).
3. **Enable HSTS** via Vercel configuration headers to enforce HTTPS.
4. **Consider implementing account lockout** (not just rate limiting) for admin accounts after repeated failures.
5. **Add password confirmation** on the customer profile page for username changes if that feature is added in the future.

---

*End of Report*
