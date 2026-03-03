# Security Policy

## Reporting Security Issues

If you discover a security vulnerability in Icinga Editor, please report it to us by emailing info@vitexsoftware.cz.

## Security Updates

### CVE-2025-46734 - Fixed in Version 2.0.1

**Issue**: Cross-site scripting (XSS) vulnerability in the league/commonmark library (versions 1.5.0 through 2.6.x) through the Attributes extension.

**Impact**: Could allow remote attackers to inject malicious JavaScript into HTML output when processing Markdown content.

**Fix Applied**:
- Updated league/commonmark dependency from `< 0.18.3` to `^2.7.0` 
- Implemented secure configuration with `html_input: 'strip'` and `allow_unsafe_links: false`
- Updated code to use the new v2.x API with proper security settings

**Affected Files**:
- `composer.json` 
- `debian/conf/composer.json`
- `src/about.php`

**Recommendation**: Users should update their installations and run `composer update` to ensure they have the secure version of league/commonmark.