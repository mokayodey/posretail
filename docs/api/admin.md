# Admin Functions API Documentation

## Overview
The Admin Functions API helps you manage your entire retail system. You can handle user accounts, system settings, and overall business management from one place.

## Getting Started
Before using admin functions, you'll need:
1. A valid API token
2. Admin-level access
3. Understanding of system requirements

## Base Endpoints
```
GET    /v1/admin/users           # Manage users
POST   /v1/admin/users           # Create user
PUT    /v1/admin/users/{user}    # Update user
DELETE /v1/admin/users/{user}    # Delete user
GET    /v1/admin/settings        # System settings
PUT    /v1/admin/settings        # Update settings
GET    /v1/admin/audit-logs      # View audit logs
POST   /v1/admin/backup          # Create backup
```

## User Management
Handle user accounts and permissions.

```http
GET /v1/admin/users
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| role | string | No | Filter by role | "admin" |
| status | string | No | Filter by status | "active" |
| branch_id | number | No | Filter by branch | 1 |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@store.com",
            "role": "admin",
            "status": "active",
            "branch": {
                "id": 1,
                "name": "Main Store"
            },
            "last_login": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Create User
Add a new user to the system.

```http
POST /v1/admin/users
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| name | string | Yes | User's name | "John Doe" |
| email | string | Yes | User's email | "john@store.com" |
| password | string | Yes | User's password | "secure123" |
| role | string | Yes | User's role | "cashier" |
| branch_id | number | No | Assigned branch | 1 |
| permissions | array | No | Custom permissions | ["view_sales", "manage_inventory"] |

### Example Request
```json
{
    "name": "John Doe",
    "email": "john@store.com",
    "password": "secure123",
    "role": "cashier",
    "branch_id": 1,
    "permissions": ["view_sales", "manage_inventory"]
}
```

## Update User
Modify user details and permissions.

```http
PUT /v1/admin/users/{user}
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| name | string | No | New name | "John Smith" |
| email | string | No | New email | "john.smith@store.com" |
| role | string | No | New role | "manager" |
| status | string | No | New status | "inactive" |
| permissions | array | No | New permissions | ["manage_staff"] |

## System Settings
Manage global system settings.

```http
GET /v1/admin/settings
```

### Example Response
```json
{
    "success": true,
    "data": {
        "company": {
            "name": "My Store",
            "logo": "logo.png",
            "address": "123 Main St",
            "phone": "08012345678",
            "email": "info@store.com"
        },
        "system": {
            "currency": "NGN",
            "timezone": "Africa/Lagos",
            "date_format": "Y-m-d",
            "tax_rate": 7.5
        },
        "notifications": {
            "email": true,
            "sms": true,
            "push": false
        }
    }
}
```

## Update Settings
Change system-wide settings.

```http
PUT /v1/admin/settings
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| company | object | No | Company details | {"name": "New Store"} |
| system | object | No | System settings | {"currency": "USD"} |
| notifications | object | No | Notification settings | {"email": false} |

## Audit Logs
View system activity history.

```http
GET /v1/admin/audit-logs
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| user_id | number | No | Filter by user | 1 |
| action | string | No | Filter by action | "login" |
| start_date | date | No | Start date | "2024-01-01" |
| end_date | date | No | End date | "2024-01-31" |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user": {
                "id": 1,
                "name": "John Doe"
            },
            "action": "login",
            "details": "Successful login",
            "ip_address": "192.168.1.1",
            "created_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Create Backup
Generate a system backup.

```http
POST /v1/admin/backup
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| type | string | Yes | Backup type | "full" |
| include_files | boolean | No | Include files | true |
| notes | string | No | Backup notes | "Monthly backup" |

### Example Request
```json
{
    "type": "full",
    "include_files": true,
    "notes": "Monthly backup"
}
```

## Common Errors

### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid request",
    "errors": {
        "email": ["Email is required"]
    }
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "Access denied"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "System error occurred"
}
```

## Tips for Success
1. Keep user permissions up to date
2. Regularly review audit logs
3. Schedule regular backups
4. Update system settings carefully
5. Monitor user activity
6. Keep security settings current
7. Use proper error handling
8. Test admin functions thoroughly

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify your permissions
3. Make sure all required fields are filled
4. Contact support if the issue persists 