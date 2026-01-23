# Security Policy

## 🔒 Supported Versions

We actively support security updates for the following Laravel versions:

| Laravel Version | Supported          |
| --------------- | ------------------ |
| Latest (LTS)    | :white_check_mark: |
| Older (< LTS)   | :x:                |

We **highly recommend** running the latest [LTS](https://laravel.com/docs/releases) (Long Term Support) release.

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability, **do not** open a public issue. Instead, report it using one of the following methods:

### Preferred Method: GitHub Security Advisory

1. Go to the **Security** tab in this repository
2. Click **"Report a vulnerability"**
3. Complete the security advisory form with details about the issue

### Alternative Method: Private Contact

If you cannot use the Security Advisory feature, please email the maintainers directly with:

- A detailed description of the vulnerability
- Steps to reproduce the issue
- Potential impact and severity assessment
- Suggested fix or mitigation (if available)

## ⏱️ Response Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Resolution**: Depends on severity and complexity

## 🛡️ Security Best Practices

When using this Laravel project:

1. **Keep dependencies updated**: Run `composer update` regularly to receive security patches
2. **Review code changes**: Audit all pull requests before merging
3. **Use environment variables**: Never commit sensitive information (`.env` values, keys, or passwords) to the repository
4. **Enforce principle of least privilege**: Only grant necessary permissions (filesystem, database users, etc.)
5. **Enable security features**: Use HTTPS, configure security-related middleware, and follow Laravel's [security recommendations](https://laravel.com/docs/security)

## 📋 Security Checklist for Contributors

- [ ] No hardcoded secrets or API keys in code
- [ ] Input validation and sanitization implemented (use Laravel validation)
- [ ] Dependencies up-to-date (`composer audit` passes)
- [ ] Security headers configured properly (use Laravel's middleware)
- [ ] Error messages don't disclose sensitive info
- [ ] Authentication and authorization flows follow best practices
- [ ] Protections against SQL injection & XSS are in place (use Eloquent/Query Builder, Blade escaping)

## 🔍 Security Audit

Regularly run Laravel and Composer security checks:

```bash
composer audit
php artisan security:check
```

See [Composer audit documentation](https://getcomposer.org/doc/03-cli.md#audit) and [Laravel security docs](https://laravel.com/docs/security).

## 📚 Additional Resources

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://paragonie.com/blog/2017/12/guidelines-for-securing-your-php-application)

Thank you for helping keep this project and its users secure!
