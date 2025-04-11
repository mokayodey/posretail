# Stock Transfers API Documentation

## Overview
The Stock Transfers API helps you move products between different store locations. You can create transfer requests, track their status, and manage inventory across branches.

## Getting Started
Before transferring stock, you'll need:
1. A valid API token
2. Access to both source and destination branches
3. Product details and quantities

## Base Endpoints
```
POST   /v1/stock-transfers              # Create transfer
GET    /v1/stock-transfers              # List transfers
GET    /v1/stock-transfers/{transfer}   # Get transfer details
PUT    /v1/stock-transfers/{transfer}   # Update transfer
POST   /v1/stock-transfers/{transfer}/approve # Approve transfer
POST   /v1/stock-transfers/{transfer}/reject  # Reject transfer
POST   /v1/stock-transfers/{transfer}/receive # Receive transfer
```

## Create Transfer
Start a new stock transfer between branches.

```http
POST /v1/stock-transfers
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| from_branch_id | number | Yes | Source branch | 1 |
| to_branch_id | number | Yes | Destination branch | 2 |
| items | array | Yes | Products to transfer | [{"product_id": 1, "quantity": 10}] |
| notes | string | No | Transfer notes | "Urgent restock" |

### Example Request
```json
{
    "from_branch_id": 1,
    "to_branch_id": 2,
    "items": [
        {
            "product_id": 1,
            "quantity": 10
        },
        {
            "product_id": 2,
            "quantity": 5
        }
    ],
    "notes": "Urgent restock"
}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "from_branch_id": 1,
        "to_branch_id": 2,
        "status": "pending",
        "items": [
            {
                "product_id": 1,
                "quantity": 10
            },
            {
                "product_id": 2,
                "quantity": 5
            }
        ],
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

## List Transfers
View all stock transfers.

```http
GET /v1/stock-transfers
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| status | string | No | Filter by status | "pending" |
| from_branch_id | number | No | Filter by source | 1 |
| to_branch_id | number | No | Filter by destination | 2 |
| start_date | date | No | Start date | "2024-01-01" |
| end_date | date | No | End date | "2024-01-31" |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "from_branch": {
                "id": 1,
                "name": "Main Store"
            },
            "to_branch": {
                "id": 2,
                "name": "Branch 2"
            },
            "status": "pending",
            "total_items": 2,
            "created_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Get Transfer Details
View information about a specific transfer.

```http
GET /v1/stock-transfers/{transfer}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "from_branch": {
            "id": 1,
            "name": "Main Store"
        },
        "to_branch": {
            "id": 2,
            "name": "Branch 2"
        },
        "status": "pending",
        "items": [
            {
                "product": {
                    "id": 1,
                    "name": "Product A"
                },
                "quantity": 10
            }
        ],
        "created_at": "2024-01-01T12:00:00.000000Z",
        "approved_at": null,
        "received_at": null
    }
}
```

## Approve Transfer
Approve a pending transfer request.

```http
POST /v1/stock-transfers/{transfer}/approve
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| notes | string | No | Approval notes | "Approved by manager" |

### Example Request
```json
{
    "notes": "Approved by manager"
}
```

## Reject Transfer
Reject a pending transfer request.

```http
POST /v1/stock-transfers/{transfer}/reject
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| reason | string | Yes | Rejection reason | "Insufficient stock" |
| notes | string | No | Additional notes | "Please check inventory" |

### Example Request
```json
{
    "reason": "Insufficient stock",
    "notes": "Please check inventory"
}
```

## Receive Transfer
Mark a transfer as received at destination.

```http
POST /v1/stock-transfers/{transfer}/receive
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| items | array | Yes | Received items | [{"product_id": 1, "quantity": 10}] |
| notes | string | No | Receipt notes | "All items received" |

### Example Request
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 10
        }
    ],
    "notes": "All items received"
}
```

## Common Errors

### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid transfer details",
    "errors": {
        "quantity": ["Must be greater than 0"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Transfer not found"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "Failed to process transfer"
}
```

## Tips for Success
1. Check stock availability before creating transfers
2. Keep transfer notes clear and detailed
3. Monitor transfer status regularly
4. Verify received quantities carefully
5. Update inventory after transfers
6. Keep track of transfer history
7. Use proper error handling
8. Test transfer operations thoroughly

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify transfer details
3. Make sure all required fields are filled
4. Contact support if the issue persists 