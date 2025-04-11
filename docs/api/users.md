# User Management API Documentation

## Overview
This API documentation covers all aspects of user management in a retail environment. Whether you're a store owner, manager, or developer, this guide will help you understand how to manage users, handle authentication, and control access to your system.

### Key Features
- **User Authentication**: Secure login and registration
- **Role Management**: Define user roles and permissions
- **Multi-Branch Support**: Manage users across multiple locations
- **Two-Factor Authentication**: Enhanced security options
- **Profile Management**: Manage user details and preferences
- **Activity Tracking**: Monitor user actions and logins
- **Password Management**: Handle password resets and changes
- **Access Control**: Role-based access control (RBAC)

## Getting Started

### Authentication
All API endpoints require authentication. Include your API token in the header:
```http
Authorization: Bearer your-api-token
```

### Base URL
All endpoints are relative to:
```http
https://api.yourstore.com
```

### Response Format
All responses follow this structure:
```json
{
    "success": true|false,
    "message": "Operation result message",
    "data": {
        // Response data
    }
}
```

## Authentication

### Understanding User Authentication
User authentication in the system:
- Verifies user identity
- Manages access tokens
- Supports multiple devices
- Provides security features
- Tracks login attempts

### Login
Authenticate a user and receive an access token.

```http
POST /api/auth/login
```

#### Request Body Explained
```json
{
    "email": "user@example.com",  // User's email address
    "password": "password123",  // User's password
    "device_info": {
        "device_id": "device_123",  // Unique device identifier
        "device_type": "mobile",  // Type of device
        "os_version": "iOS 15.0"  // Operating system version
    }
}
```

#### Example Scenario
A cashier logging in at the start of their shift:
```json
{
    "email": "cashier@store.com",
    "password": "secure123",
    "device_info": {
        "device_id": "POS-001",
        "device_type": "pos_terminal",
        "os_version": "Windows 10"
    }
}
```

#### Response Explained
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,  // Unique user identifier
            "name": "John Doe",  // User's full name
            "email": "user@example.com",  // User's email
            "role": "manager",  // User's role
            "branches": [
                {
                    "id": 1,
                    "name": "Main Branch",  // Assigned branch
                    "pivot": {
                        "is_primary": true  // Primary work location
                    }
                }
            ],
            "permissions": [  // User's permissions
                "view_sales",
                "manage_inventory",
                "process_payments"
            ]
        },
        "token": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",  // JWT token
            "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",  // Refresh token
            "token_type": "Bearer",  // Token type
            "expires_in": 3600  // Token expiry in seconds
        },
        "requires_2fa": false  // Whether 2FA is required
    }
}
```

### Two-Factor Authentication (2FA)
Add an extra layer of security to user accounts.

#### Enable 2FA
```http
POST /api/auth/2fa/enable
```

#### Request Body Explained
```json
{
    "method": "authenticator",  // 2FA method (authenticator, sms, email)
    "phone": "08012345678"  // Required for SMS method
}
```

#### Verify 2FA
```http
POST /api/auth/2fa/verify
```

#### Request Body Explained
```json
{
    "code": "123456",  // 6-digit verification code
    "method": "authenticator"  // Method used for verification
}
```

### Register
Create a new user account.

```http
POST /api/auth/register
```

#### Request Body Explained
```json
{
    "name": "John Doe",  // Full name
    "email": "user@example.com",  // Email address
    "password": "password123",  // Password
    "password_confirmation": "password123",  // Password confirmation
    "role": "cashier",  // User role
    "branches": [  // Branch assignments
        {
            "branch_id": 1,
            "is_primary": true  // Primary work location
        }
    ],
    "profile": {  // Additional user information
        "phone_number": "08012345678",
        "address": "123 Main St",
        "emergency_contact": {
            "name": "Jane Doe",
            "phone": "08098765432"
        }
    }
}
```

#### Example Scenario
Creating a new cashier account:
```json
{
    "name": "Sarah Johnson",
    "email": "sarah@store.com",
    "password": "secure456",
    "password_confirmation": "secure456",
    "role": "cashier",
    "branches": [
        {
            "branch_id": 1,
            "is_primary": true
        }
    ],
    "profile": {
        "phone_number": "08012345678",
        "address": "456 Store St",
        "emergency_contact": {
            "name": "Mike Johnson",
            "phone": "08087654321"
        }
    }
}
```

### Password Management

#### Forgot Password
Request a password reset link.

```http
POST /api/auth/password/forgot
```

#### Request Body Explained
```json
{
    "email": "user@example.com"  // User's email address
}
```

#### Reset Password
Reset password using reset token.

```http
POST /api/auth/password/reset
```

#### Request Body Explained
```json
{
    "email": "user@example.com",  // User's email
    "token": "reset_token_123",  // Reset token from email
    "password": "new_password123",  // New password
    "password_confirmation": "new_password123"  // Confirm new password
}
```

## User Management

### Understanding User Roles
The system supports various user roles:
- **Admin**: Full system access
- **Manager**: Branch management access
- **Cashier**: Sales and basic operations
- **Inventory Clerk**: Stock management
- **Accountant**: Financial operations

### List Users
View all users with filtering options.

```http
GET /api/users
```

#### Query Parameters Explained
- `role`: Filter by user role (e.g., cashier, manager)
- `branch_id`: Filter by branch location
- `search`: Search by name or email
- `status`: Filter by user status (active, inactive)
- `include`: Include related data
- `sort`: Sort results
- `per_page`: Results per page

#### Example Request
```http
GET /api/users?role=cashier&branch_id=1&status=active&include=profile,permissions
```

## Best Practices

### For Store Owners
1. Regularly review user access
2. Enforce strong password policies
3. Enable 2FA for sensitive roles
4. Monitor user activity
5. Keep user information updated

### For Managers
1. Verify user identities before creation
2. Assign appropriate roles
3. Update branch assignments promptly
4. Review access permissions regularly
5. Handle password resets securely

### For Developers
1. Implement proper authentication
2. Validate all user inputs
3. Handle errors gracefully
4. Log security events
5. Follow API best practices

## Frequently Asked Questions

### Q: How do I reset a user's password?
A: Use the password reset endpoint and send the reset link to their email.

### Q: What should I do if a user is locked out?
A: Check their status and login attempts, then reset their password if needed.

### Q: How do I manage user permissions?
A: Assign appropriate roles and customize permissions as needed.

### Q: What happens when a user changes branches?
A: Update their branch assignments and ensure proper access levels.

## Support
For additional help or questions:
- Email: support@yourstore.com
- Phone: +234 800 123 4567
- Hours: Monday - Friday, 9:00 AM - 5:00 PM 