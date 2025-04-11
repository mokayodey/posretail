# Payment Processing API Documentation

## Overview
The Payment Processing API helps you handle different types of payments in your store. Whether customers pay with cash, bank transfer, or Moniepoint terminal, this API makes it easy to process payments and track transactions.

## Getting Started
Before processing payments, you'll need:
1. A valid API token
2. A completed cart (from Cart Management API)
3. Payment details from the customer

## Base Endpoints
```
POST   /v1/payments/bank-transfer/{cart}    # Process bank transfer
POST   /v1/payments/moniepoint/{cart}       # Process Moniepoint payment
POST   /v1/payments/confirm/{payment}       # Confirm a payment
GET    /v1/payments/pending                 # Get pending payments
GET    /v1/payments/history                 # Get payment history
```

## Process Bank Transfer
Handle payments made through bank transfers.

```http
POST /v1/payments/bank-transfer/{cart}
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| bank_name | string | Yes | Customer's bank | "GTBank" |
| account_number | string | Yes | Customer's account | "0123456789" |
| amount | number | Yes | Payment amount | 1000.00 |
| reference | string | No | Transfer reference | "TRF123456" |
| notes | string | No | Additional notes | "Payment for order #123" |

### Example Request
```json
{
    "bank_name": "GTBank",
    "account_number": "0123456789",
    "amount": 1000.00,
    "reference": "TRF123456",
    "notes": "Payment for order #123"
}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "cart_id": 1,
        "payment_method": "bank_transfer",
        "amount": 1000.00,
        "status": "pending",
        "reference": "TRF123456",
        "created_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

## Process Moniepoint Payment
Handle payments made through Moniepoint terminals.

```http
POST /v1/payments/moniepoint/{cart}
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| terminal_id | string | Yes | Terminal ID | "MPT001" |
| amount | number | Yes | Payment amount | 1000.00 |
| reference | string | No | Transaction reference | "MPT123456" |
| notes | string | No | Additional notes | "Card payment" |

### Example Request
```json
{
    "terminal_id": "MPT001",
    "amount": 1000.00,
    "reference": "MPT123456",
    "notes": "Card payment"
}
```

## Confirm Payment
Mark a payment as confirmed after verifying the transfer.

```http
POST /v1/payments/confirm/{payment}
```

### What You Need
| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| reference | string | Yes | Payment reference | "TRF123456" |
| amount | number | Yes | Confirmed amount | 1000.00 |
| payment_method | string | Yes | Payment method | "bank_transfer" |

### Example Request
```json
{
    "reference": "TRF123456",
    "amount": 1000.00,
    "payment_method": "bank_transfer"
}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "cart_id": 1,
        "payment_method": "bank_transfer",
        "amount": 1000.00,
        "status": "completed",
        "receipt_number": "REC001",
        "confirmed_at": "2024-01-01T12:30:00.000000Z"
    }
}
```

## Get Pending Payments
View all payments waiting for confirmation.

```http
GET /v1/payments/pending
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| payment_method | string | No | Filter by method | "bank_transfer" |
| branch_id | number | No | Filter by branch | 1 |
| start_date | date | No | Start date | "2024-01-01" |
| end_date | date | No | End date | "2024-01-31" |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "cart_id": 1,
            "payment_method": "bank_transfer",
            "amount": 1000.00,
            "status": "pending",
            "reference": "TRF123456",
            "created_at": "2024-01-01T12:00:00.000000Z"
        }
    ]
}
```

## Get Payment History
View all processed payments.

```http
GET /v1/payments/history
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| payment_method | string | No | Filter by method | "bank_transfer" |
| status | string | No | Filter by status | "completed" |
| branch_id | number | No | Filter by branch | 1 |
| start_date | date | No | Start date | "2024-01-01" |
| end_date | date | No | End date | "2024-01-31" |

### Example Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "cart_id": 1,
            "payment_method": "bank_transfer",
            "amount": 1000.00,
            "status": "completed",
            "reference": "TRF123456",
            "receipt_number": "REC001",
            "created_at": "2024-01-01T12:00:00.000000Z",
            "confirmed_at": "2024-01-01T12:30:00.000000Z"
        }
    ]
}
```

## Common Errors

### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid payment details",
    "errors": {
        "amount": ["Must be greater than 0"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Payment not found"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "Payment processing failed"
}
```

## Tips for Success
1. Always verify payment details before confirming
2. Keep track of payment references
3. Save receipt numbers for future reference
4. Monitor pending payments regularly
5. Double-check amounts before confirming
6. Keep backup of payment records
7. Use proper error handling
8. Test payment processing thoroughly

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify payment details
3. Make sure all required fields are filled
4. Contact support if the issue persists 