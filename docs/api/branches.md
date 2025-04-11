# Branch Management API Documentation

## Overview
The Branch Management API helps you manage different store locations. You can create new branches, update their details, assign staff, and track their performance.

## Getting Started
Before managing branches, you'll need:
1. A valid API token
2. Admin or manager access
3. Branch details ready

## Base Endpoints
```
GET    /v1/branches              # List all branches
POST   /v1/branches              # Create new branch
GET    /v1/branches/{branch}     # Get branch details
PUT    /v1/branches/{branch}     # Update branch
DELETE /v1/branches/{branch}     # Delete branch
POST   /v1/branches/{branch}/staff # Assign staff
GET    /v1/branches/{branch}/performance # Get performance
```

## List All Branches
View all your store locations.

```http
GET /v1/branches
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| status | string | No | Filter by status | "active" |
| search | string | No | Search by name | "Main" |
| sort | string | No | Sort by field | "name" |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Main Store",
            "code": "MS001",
            "address": "123 Main St",
            "phone": "08012345678",
            "email": "main@store.com",
            "status": "active",
            "created_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Create New Branch
Add a new store location.

```http
POST /v1/branches
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| name | string | Yes | Branch name | "Main Store" |
| code | string | Yes | Unique code | "MS001" |
| address | string | Yes | Full address | "123 Main St" |
| phone | string | Yes | Contact phone | "08012345678" |
| email | string | Yes | Contact email | "main@store.com" |
| manager_id | number | No | Manager's ID | 1 |
| opening_hours | json | No | Operating hours | {"monday": "9:00-18:00"} |

### Example Request
```json
{
    "name": "Main Store",
    "code": "MS001",
    "address": "123 Main St",
    "phone": "08012345678",
    "email": "main@store.com",
    "manager_id": 1,
    "opening_hours": {
        "monday": "9:00-18:00",
        "tuesday": "9:00-18:00",
        "wednesday": "9:00-18:00",
        "thursday": "9:00-18:00",
        "friday": "9:00-18:00",
        "saturday": "10:00-16:00",
        "sunday": "closed"
    }
}
```

## Get Branch Details
View information about a specific branch.

```http
GET /v1/branches/{branch}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Main Store",
        "code": "MS001",
        "address": "123 Main St",
        "phone": "08012345678",
        "email": "main@store.com",
        "status": "active",
        "manager": {
            "id": 1,
            "name": "John Doe"
        },
        "staff_count": 5,
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

## Update Branch
Modify branch details.

```http
PUT /v1/branches/{branch}
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| name | string | No | New name | "Main Store 2" |
| address | string | No | New address | "456 Main St" |
| phone | string | No | New phone | "08087654321" |
| email | string | No | New email | "main2@store.com" |
| status | string | No | New status | "inactive" |
| opening_hours | json | No | New hours | {"monday": "10:00-19:00"} |

## Assign Staff
Add or remove staff from a branch.

```http
POST /v1/branches/{branch}/staff
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| user_id | number | Yes | Staff ID | 2 |
| role | string | Yes | Staff role | "cashier" |
| start_date | date | No | Start date | "2024-01-01" |

### Example Request
```json
{
    "user_id": 2,
    "role": "cashier",
    "start_date": "2024-01-01"
}
```

## Get Branch Performance
View sales and performance metrics.

```http
GET /v1/branches/{branch}/performance
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| start_date | date | No | Start date | "2024-01-01" |
| end_date | date | No | End date | "2024-01-31" |
| metric | string | No | Performance metric | "sales" |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_sales": 100000.00,
        "total_orders": 500,
        "average_order_value": 200.00,
        "top_products": [
            {
                "name": "Product A",
                "quantity": 100,
                "revenue": 20000.00
            }
        ],
        "period": {
            "start": "2024-01-01",
            "end": "2024-01-31"
        }
    }
}
```

## Common Errors

### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid branch details",
    "errors": {
        "name": ["Name is required"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Branch not found"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "Failed to process branch request"
}
```

## Tips for Success
1. Keep branch codes unique and meaningful
2. Update contact details regularly
3. Monitor branch performance
4. Assign appropriate staff roles
5. Keep track of operating hours
6. Maintain accurate addresses
7. Use proper error handling
8. Test branch operations thoroughly

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify branch details
3. Make sure all required fields are filled
4. Contact support if the issue persists 