# Customer Loyalty Program API Documentation

## Overview
The Customer Loyalty Program API helps you manage customer relationships and rewards. You can track customer purchases, award loyalty points, and manage rewards.

## Getting Started
Before using the loyalty program, you'll need:
1. A valid API token
2. Customer information
3. Understanding of points system

## Base Endpoints
```
GET    /v1/customers              # List customers
POST   /v1/customers              # Create customer
GET    /v1/customers/{customer}   # Get customer details
PUT    /v1/customers/{customer}   # Update customer
POST   /v1/customers/{customer}/points          # Add points
POST   /v1/customers/{customer}/points/redeem   # Redeem points
GET    /v1/customers/{customer}/points/history  # Points history
GET    /v1/customers/{customer}/rewards         # Available rewards
POST   /v1/customers/{customer}/rewards         # Create reward
POST   /v1/customers/{customer}/rewards/{reward}/redeem  # Redeem reward
```

## List Customers
Get a list of all customers with optional filtering.

```http
GET /v1/customers
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| search | string | No | Search by name/email/phone | "john" |
| membership_tier | string | No | Filter by tier | "gold" |
| status | string | No | Filter by status | "active" |
| sort | string | No | Sort field | "name" |
| direction | string | No | Sort direction | "asc" |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "08012345678",
            "loyalty_points": 1500,
            "membership_tier": "silver",
            "total_spent": 50000.00,
            "last_purchase_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Create Customer
Add a new customer to the system.

```http
POST /v1/customers
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| name | string | Yes | Customer's name | "John Doe" |
| email | string | Yes | Customer's email | "john@example.com" |
| phone | string | No | Customer's phone | "08012345678" |
| address | string | No | Customer's address | "123 Main St" |
| birth_date | date | No | Customer's birthday | "1990-01-01" |
| anniversary_date | date | No | Anniversary date | "2020-01-01" |
| preferences | object | No | Customer preferences | {"newsletter": true} |

### Example Request
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08012345678",
    "address": "123 Main St",
    "birth_date": "1990-01-01",
    "preferences": {
        "newsletter": true,
        "sms_notifications": true
    }
}
```

## Add Points
Award loyalty points to a customer.

```http
POST /v1/customers/{customer}/points
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| points | integer | Yes | Points to add | 100 |
| source | string | Yes | Source of points | "purchase" |
| description | string | No | Description | "Birthday bonus" |
| sale_id | integer | No | Related sale ID | 1 |

### Example Request
```json
{
    "points": 100,
    "source": "purchase",
    "description": "Birthday bonus",
    "sale_id": 1
}
```

## Redeem Points
Let a customer use their points.

```http
POST /v1/customers/{customer}/points/redeem
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| points | integer | Yes | Points to redeem | 50 |
| description | string | No | Description | "Discount on purchase" |

### Example Request
```json
{
    "points": 50,
    "description": "Discount on purchase"
}
```

## Points History
View a customer's points transactions.

```http
GET /v1/customers/{customer}/points/history
```

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "points": 100,
            "type": "earn",
            "source": "purchase",
            "description": "Birthday bonus",
            "created_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Available Rewards
Get rewards a customer can claim.

```http
GET /v1/customers/{customer}/rewards
```

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "10% Discount",
            "description": "Get 10% off your next purchase",
            "points_cost": 500,
            "expires_at": "2024-12-31T23:59:59.000000Z"
        }
    ]
}
```

## Create Reward
Add a new reward for a customer.

```http
POST /v1/customers/{customer}/rewards
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| name | string | Yes | Reward name | "10% Discount" |
| description | string | No | Reward details | "Get 10% off" |
| points_cost | integer | Yes | Points needed | 500 |
| expires_at | datetime | No | Expiry date | "2024-12-31" |

### Example Request
```json
{
    "name": "10% Discount",
    "description": "Get 10% off your next purchase",
    "points_cost": 500,
    "expires_at": "2024-12-31T23:59:59.000000Z"
}
```

## Redeem Reward
Let a customer claim a reward.

```http
POST /v1/customers/{customer}/rewards/{reward}/redeem
```

### Example Response
```json
{
    "success": true,
    "message": "Reward redeemed successfully",
    "data": {
        "id": 1,
        "name": "10% Discount",
        "status": "redeemed",
        "redeemed_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

## Membership Tiers
| Tier | Points Required | Discount | Benefits |
|------|----------------|----------|----------|
| Bronze | 0 | 5% | Basic rewards |
| Silver | 1,000 | 10% | Priority service |
| Gold | 5,000 | 15% | Exclusive offers |
| Platinum | 10,000 | 20% | VIP treatment |

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

### 404 Not Found
```json
{
    "success": false,
    "message": "Customer not found"
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
1. Keep customer data up to date
2. Award points promptly
3. Create attractive rewards
4. Monitor point balances
5. Send birthday rewards
6. Track reward redemptions
7. Use proper error handling
8. Test reward system thoroughly

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify customer exists
3. Ensure enough points
4. Contact support if needed 