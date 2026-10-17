# 0004. Two-factor authentication is enforced in WordPress' `authenticate` filter

- **Status:** accepted
- **Date:** 2026-10-17

## Context

Alma v7 checked 2FA with a session flag in Laravel middleware on its own routes. WordPress pages, wp-admin and wp-login.php never run that middleware, so a password alone was enough there. The flow also looped: the challenge route required the login it was meant to complete.

## Decision

The 2FA gate hooks `authenticate` late. A correct password from a 2FA user starts a pending challenge (a random token in an HttpOnly cookie, state in a hashed transient: 10 minutes, 5 attempts) and the login is refused. The WordPress auth cookie is only issued after a valid TOTP or recovery code. Being logged in therefore implies 2FA passed, everywhere.

## Consequences

wp-login.php, wp-admin and the Livewire login are all covered, and no session flag is needed. Application Passwords (REST, MCP) bypass 2FA by design, because scripts can't answer a code prompt. They are separate, revocable credentials.
