# Security Policy

## Supported versions

The `main` branch receives security fixes.

## Reporting a vulnerability

**Do not open a public issue for security vulnerabilities.**

Report privately via [GitHub Security Advisories](https://github.com/WAHIB-EL-KHADIRI/taskflow-pro/security/advisories/new),
or email **wahibelkhadiri06@gmail.com**.

Please include:

- what the vulnerability allows an attacker to do
- steps to reproduce it
- the affected files or endpoints, if you know them

You will get an acknowledgement within 72 hours and an assessment within a week.
If the report is valid, you will be credited in the fix release unless you prefer
otherwise.

## Scope

In scope: authentication and session handling, input sanitization, SQL injection,
XSS, CSRF, file upload handling, and access control between workspaces.

Out of scope: issues that require an already-compromised server or database, and
missing hardening in the local development setup (`composer start`), which is not
intended for production use.
