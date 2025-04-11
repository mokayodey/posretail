# Cart Management API Documentation

## Overview
The Cart Management API helps you handle shopping carts in your store. Think of it like a digital shopping basket where customers can add items before checking out. This API lets you:
- Create new shopping carts
- Add or remove items
- Apply discounts
- Generate receipts
- Cancel transactions

## Getting Started
Before using the Cart API, you'll need:
1. A valid API token (ask your system administrator)
2. Product IDs of items you want to add
3. Branch ID where the sale is happening

## Base Endpoints
```
POST   /v1/carts              # Create a new cart
GET    /v1/carts/{cart}       # Get cart details
POST   /v1/carts/{cart}/items # Add items to cart
PUT    /v1/carts/{cart}/items/{item} # Update cart item
DELETE /v1/carts/{cart}/items/{item} # Remove item from cart
POST   /v1/carts/{cart}/checkout # Process payment
POST   /v1/carts/{cart}/void   # Cancel transaction
GET    /v1/carts/{cart}/receipt # Generate receipt
```

## Create a New Cart
Start a new shopping cart for a customer.

```http
POST /v1/carts
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| branch_id | number | Yes | Which store location | 1 |
| customer_id | number | No | Customer's ID (if known) | 123 |
| cashier_id | number | Yes | Staff member's ID | 456 |
| notes | string | No | Any special notes | "Customer prefers paper bag" |

### Example Request
```json
{
    "branch_id": 1,
    "customer_id": 123,
    "cashier_id": 456,
    "notes": "Customer prefers paper bag"
}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "branch_id": 1,
        "customer_id": 123,
        "cashier_id": 456,
        "status": "open",
        "total": 0.00,
        "items": [],
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

## Add Items to Cart
Add products to the shopping cart.

```http
POST /v1/carts/{cart}/items
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| product_id | number | Yes | Product's unique ID | 789 |
| quantity | number | Yes | How many to add | 2 |
| price | number | No | Special price (if different) | 19.99 |
| notes | string | No | Any special notes | "Gift wrapped" |

### Example Request
```json
{
    "product_id": 789,
    "quantity": 2,
    "price": 19.99,
    "notes": "Gift wrapped"
}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "cart_id": 1,
        "product_id": 789,
        "quantity": 2,
        "price": 19.99,
        "total": 39.98,
        "notes": "Gift wrapped"
    }
}
```

## Update Cart Item
Change quantity or price of an item in the cart.

```http
PUT /v1/carts/{cart}/items/{item}
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| quantity | number | No | New quantity | 3 |
| price | number | No | New price | 15.99 |
| notes | string | No | Updated notes | "Changed to blue color" |

### Example Request
```json
{
    "quantity": 3,
    "price": 15.99,
    "notes": "Changed to blue color"
}
```

## Remove Item from Cart
Take an item out of the cart.

```http
DELETE /v1/carts/{cart}/items/{item}
```

### Example Response
```json
{
    "success": true,
    "message": "Item removed from cart"
}
```

## Process Payment
Complete the sale and process payment.

```http
POST /v1/carts/{cart}/checkout
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| payment_method | string | Yes | How customer is paying | "cash" |
| amount_paid | number | Yes | Amount customer paid | 100.00 |
| reference | string | No | Payment reference | "CASH001" |
| notes | string | No | Payment notes | "Paid in full" |

### Payment Methods
- `cash`: Physical cash payment
- `bank_transfer`: Bank transfer
- `moniepoint`: Moniepoint terminal
- `suregifts`: Suregifts voucher

### Example Request
```json
{
    "payment_method": "cash",
    "amount_paid": 100.00,
    "reference": "CASH001",
    "notes": "Paid in full"
}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "cart_id": 1,
        "payment_method": "cash",
        "amount_paid": 100.00,
        "change": 0.00,
        "status": "completed",
        "receipt_number": "REC001",
        "created_at": "2024-01-01T12:30:00.000000Z"
    }
}
```

## Generate Receipt
Get a receipt for the completed sale.

```http
GET /v1/carts/{cart}/receipt
```

### Example Response
```json
{
    "success": true,
    "data": {
        "receipt_number": "REC001",
        "date": "2024-01-01T12:30:00.000000Z",
        "items": [
            {
                "name": "Blue Shirt",
                "quantity": 2,
                "price": 19.99,
                "total": 39.98
            }
        ],
        "subtotal": 39.98,
        "tax": 3.20,
        "total": 43.18,
        "payment_method": "cash",
        "amount_paid": 100.00,
        "change": 56.82,
        "cashier": "John Doe",
        "branch": "Main Store"
    }
}
```

## Cancel Transaction
Void a transaction if needed.

```http
POST /v1/carts/{cart}/void
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| reason | string | Yes | Why canceling | "Customer changed mind" |
| notes | string | No | Additional notes | "Refund processed" |

### Example Request
```json
{
    "reason": "Customer changed mind",
    "notes": "Refund processed"
}
```

## Common Errors

### 400 Bad Request
```json
{
    "success": false,
    "message": "Something's wrong with your request",
    "errors": {
        "quantity": ["Must be greater than 0"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Cart not found"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "Something went wrong on our end"
}
```

## Tips for Success
1. Always check if items are in stock before adding to cart
2. Keep track of cart IDs for reference
3. Save receipt numbers for future reference
4. Handle errors gracefully in your code
5. Test payment processing thoroughly
6. Keep backup of important transactions
7. Monitor cart status changes
8. Use proper error handling

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify your API token
3. Make sure all required fields are filled
4. Contact support if the issue persists 