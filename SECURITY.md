# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 2.x     | :white_check_mark: |
| 1.x     | :x:                |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability, please email **anilkumarthakur60@gmail.com** with:

- A description of the vulnerability
- Steps to reproduce
- Potential impact
- Any suggested fix (optional)

You will receive a response within **48 hours** acknowledging receipt. We aim to release a patch within **7 days** for confirmed vulnerabilities.

## Security Measures in This Package

- **XSS Prevention**: All comment content is rendered through Parsedown in safe mode, which strips raw HTML.
- **CSRF Protection**: All routes use the `web` middleware group by default, which includes CSRF verification.
- **Spam Protection**: Guest comments are protected by [spatie/laravel-honeypot](https://github.com/spatie/laravel-honeypot).
- **Rate Limiting**: Configurable throttling on all write endpoints.
- **Authorization**: All actions are gated via Laravel's Gate/Policy system.
- **Input Validation**: All inputs are validated before processing.
