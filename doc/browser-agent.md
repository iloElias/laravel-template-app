# Browser Agent System Documentation

## Overview

The Browser Agent system is designed to track and validate user browsers and sessions within the application. It provides security mechanisms to ensure that only valid browser instances can access the application.

## Database Structure

The system uses the following tables in the `hr` schema:

1. **browser_agent**: Stores information about unique browser instances
    - `id`: Primary key
    - `fingerprint`: Unique identifier for the browser (UUID)
    - `user_agent`: The User-Agent string from the browser
    - `ip_address`: User's IP address
    - `active`: Flag indicating if the browser agent is still active
    - Timestamps: `created_at`, `updated_at`, `inactivated_at`

2. **session**: Tracks user authentication sessions
    - Links users to browser agents
    - Tracks authentication method and activity

3. **remember_browser**: Stores browser instances that users have chosen to "remember"
    - Associates users with their trusted browsers

4. **request_history**: Logs API requests
    - Records route, method, and payload information

## Key Components

### BrowserAgent Model

- Represents a unique browser instance
- Provides methods to:
  - Create new browser agents with UUID fingerprints
  - Manage relationships with sessions and remembered browsers
  - Deactivate browser agents when necessary

### BrowserAgentController

- Exposes endpoints to:
  - Generate new browser fingerprints (`makeFingerprint`)
  - Validate existing fingerprints (`validate`)

### BrowserAgentMiddleware

- Guards routes by validating browser fingerprints
- Checks the `Browser-Agent` header against stored fingerprints
- Returns appropriate error responses for missing or invalid fingerprints
- Note: Currently only enforced in non-local environments

## Usage Flow

1. Client requests a fingerprint via the BrowserAgentController
2. Client stores the fingerprint and includes it in the `Browser-Agent` header
3. BrowserAgentMiddleware validates the fingerprint on protected routes
4. Sessions and request history are tracked for security and audit purposes
