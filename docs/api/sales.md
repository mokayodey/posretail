# Sales & Transactions API Documentation

## Overview
This API documentation covers all aspects of physical point of sale (POS) operations. Whether you're a cashier, store manager, or developer, this guide will help you understand how to process sales, handle payments, and manage transactions in a retail environment.

### Key Features
- **Cart Management**: Create and manage shopping carts for customers
- **Payment Processing**: Support for multiple payment methods (cash, card, bank transfer)
- **Moniepoint Integration**: Seamless integration with Moniepoint payment terminals
- **Transaction History**: Track and manage sales transactions
- **Returns & Refunds**: Handle product returns and process refunds
- **Customer Management**: Manage customer information and loyalty programs
- **Discounts & Promotions**: Apply discounts and run promotional campaigns
- **Receipt Management**: Generate and send receipts to customers

## Getting Started

### Authentication
All API endpoints require authentication. You'll need to include an API token in the header of your requests:
```http
Authorization: Bearer your-api-token
```

### Base URL
All API endpoints are relative to the base URL:
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

## Cart Management

### What is a Cart?
A cart is a temporary container that holds items a customer wants to purchase. It's like a digital shopping basket that tracks:
- Products and quantities
- Prices and discounts
- Tax calculations
- Payment information

### Create Cart
Create a new shopping cart for a customer's purchase.

```http
POST /api/carts
```

#### Request Body Explained
```json
{
    "branch_id": "required|exists:branches,id",  // The store location where the sale is happening
    "customer_id": "nullable|exists:customers,id",  // Optional: Link to customer profile
    "location": "nullable|string",  // Optional: Specific location in store (e.g., "Checkout 1")
    "notes": "nullable|string"  // Optional: Any special notes about the transaction
}
```

#### Example Scenario
A customer walks into your store and wants to make a purchase. You create a cart:
```json
{
    "branch_id": 1,
    "location": "Checkout 1",
    "notes": "Customer in a hurry"
}
```

#### Response Explained
```json
{
    "success": true,
    "data": {
        "id": 1,  // Unique cart identifier
        "branch_id": 1,  // Store location
        "cashier_id": 1,  // Automatically assigned to current user
        "customer_id": null,  // No customer linked yet
        "transaction_code": "TRX-ABC123",  // Unique transaction reference
        "status": "active",  // Cart is ready for items
        "subtotal": 0,  // Initial amount before items
        "discount_amount": 0,  // No discounts yet
        "tax_amount": 0,  // No tax calculated yet
        "total": 0,  // Total amount (will update as items are added)
        "created_at": "2024-04-10T10:00:00.000000Z",
        "updated_at": "2024-04-10T10:00:00.000000Z"
    }
}
```

### Add Item to Cart
Add products to the cart using either product ID or barcode scanning.

```http
POST /api/carts/{cart}/items
```

#### Request Body Explained
```json
{
    "product_id": "required_without:barcode|exists:products,id",  // Product identifier
    "barcode": "required_without:product_id|string",  // Barcode number
    "quantity": "required|integer|min:1",  // Number of items
    "price_override": "nullable|numeric|min:0",  // Optional: Special price
    "location": "nullable|string",  // Optional: Item location
    "notes": "nullable|string"  // Optional: Special instructions
}
```

#### Example Scenarios

1. **Using Product ID**:
```json
{
    "product_id": 1,
    "quantity": 2,
    "notes": "Customer requested gift wrapping"
}
```

2. **Using Barcode**:
```json
{
    "barcode": "123456789012",
    "quantity": 1
}
```

#### Response Explained
```json
{
    "success": true,
    "message": "Item added to cart successfully",
    "data": {
        "cart": {
            "id": 1,
            "items": [
                {
                    "id": 1,
                    "product_id": 1,
                    "quantity": 2,
                    "price": 1000.00,  // Price per item
                    "total": 2000.00   // Total for this item (price × quantity)
                }
            ],
            "subtotal": 2000.00,  // Sum of all items
            "total": 2000.00      // Final amount including discounts and tax
        }
    }
}
```

## Payment Processing

### Understanding Payment Methods
The system supports multiple payment methods:
1. **Cash**: Physical money transactions
2. **Moniepoint Terminal**: Card payments through Moniepoint device
3. **Bank Transfer**: Direct bank transfers
4. **Moniepoint Transfer**: Transfers through Moniepoint

### Cash Payment
Process a payment using physical cash.

```http
POST /api/payments/cash/{cart}
```

#### Request Body Explained
```json
{
    "amount_received": "required|numeric|min:cart_total",  // Cash given by customer
    "notes": "nullable|string"  // Optional: Any special notes
}
```

#### Example Scenario
A customer wants to pay ₦1,000 in cash:
```json
{
    "amount_received": 1000.00,
    "notes": "Paid with ₦1000 note"
}
```

#### Response Explained
```json
{
    "success": true,
    "message": "Cash payment processed successfully",
    "data": {
        "payment": {
            "id": 1,
            "amount": 1000.00,  // Amount paid
            "payment_method": "cash",
            "status": "completed",  // Payment is complete
            "receipt_number": "RCP-ABC123",  // Receipt reference
            "change": 0.00  // No change needed
        }
    }
}
```

### Moniepoint Terminal Payment
Process a card payment using the Moniepoint terminal.

```http
POST /api/payments/moniepoint/{cart}
```

#### Request Body Explained
```json
{
    "terminal_id": "required|string",  // Moniepoint device identifier
    "notes": "nullable|string"  // Optional: Transaction notes
}
```

#### Example Scenario
A customer wants to pay with their card:
```json
{
    "terminal_id": "MP123456",
    "notes": "Customer used Visa card"
}
```

#### Response Explained
```json
{
    "success": true,
    "message": "Moniepoint payment processed successfully",
    "data": {
        "payment": {
            "id": 1,
            "amount": 1000.00,
            "payment_method": "moniepoint",
            "status": "completed",
            "receipt_number": "RCP-ABC123",
            "terminal_response": {
                "card_type": "visa",  // Type of card used
                "last_four": "1234"   // Last 4 digits of card
            }
        }
    }
}
```

### Bank Transfer Payment
Process a payment through bank transfer with immediate confirmation option.

```http
POST /api/payments/bank-transfer/{cart}
```

#### Request Body Explained
```json
{
    "bank_name": "required|string",  // Name of the bank
    "account_number": "required|string",  // Customer's account number
    "account_name": "required|string",  // Name on the account
    "reference": "required|string",  // Transfer reference number
    "amount": "required|numeric|min:0|max:cart_total",  // Transfer amount
    "notes": "nullable|string",  // Optional: Additional notes
    "confirm": "required|boolean"  // Whether to confirm immediately
}
```

#### Example Scenario
A customer wants to pay via bank transfer:
```json
{
    "bank_name": "Access Bank",
    "account_number": "1234567890",
    "account_name": "John Doe",
    "reference": "TRF123456",
    "amount": 1000.00,
    "confirm": true
}
```

#### Response Explained
```json
{
    "success": true,
    "message": "Bank transfer payment processed and confirmed",
    "data": {
        "payment": {
            "id": 1,
            "amount": 1000.00,
            "payment_method": "bank_transfer",
            "status": "completed",
            "receipt_number": "RCP-ABC123",
            "payment_details": {
                "bank_name": "Access Bank",
                "account_number": "1234567890",
                "account_name": "John Doe",
                "reference": "TRF123456"
            }
        }
    }
}
```

## Transaction History

### Understanding Transaction Statuses
Transactions can have the following statuses:
- **pending**: Payment not yet completed
- **completed**: Payment successful
- **cancelled**: Transaction cancelled
- **refunded**: Money returned to customer

### List Transactions
View all transactions with various filtering options.

```http
GET /api/transactions
```

#### Query Parameters Explained
- `branch_id`: Filter by specific store location
- `start_date`: Filter transactions from this date
- `end_date`: Filter transactions until this date
- `status`: Filter by transaction status
- `payment_method`: Filter by how customer paid
- `customer_id`: Filter by specific customer
- `min_amount`: Filter by minimum transaction amount
- `max_amount`: Filter by maximum transaction amount
- `include`: Include related data (items, customer, payments)
- `sort`: Sort results (e.g., newest first, highest amount)
- `per_page`: Number of results per page

#### Example Request
```http
GET /api/transactions?branch_id=1&start_date=2024-04-01&end_date=2024-04-10&status=completed&payment_method=cash&per_page=20
```

## Error Handling

### Common Error Types
1. **Validation Errors**: When input data is invalid
2. **Authentication Errors**: When API token is missing or invalid
3. **Payment Errors**: When payment processing fails
4. **System Errors**: When something unexpected occurs

### Example Error Responses

#### Validation Error
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "amount_received": [
            "The amount received must be greater than or equal to the cart total."
        ]
    }
}
```

#### Payment Error
```json
{
    "success": false,
    "message": "Failed to process payment",
    "error": "Insufficient funds"
}
```

## Best Practices

### For Cashiers
1. Always verify the cart total before processing payment
2. Double-check cash amounts received
3. Ensure receipt is printed for every transaction
4. Keep track of transaction codes for reference

### For Store Managers
1. Regularly review pending transactions
2. Monitor payment terminal status
3. Check for unmatched transfers daily
4. Review transaction reports regularly

### For Developers
1. Always handle errors gracefully
2. Implement proper validation
3. Use appropriate payment methods
4. Follow security best practices

## Frequently Asked Questions

### Q: How do I handle a failed payment?
A: Check the error message, verify the payment details, and try again. If the issue persists, contact support.

### Q: What should I do if a customer wants to pay partially?
A: You can process multiple payments for the same cart using different payment methods.

### Q: How do I handle returns?
A: Use the returns endpoint to create a return and then process the refund using the appropriate method.

### Q: What happens if the system goes offline?
A: The system will queue transactions and process them when back online. Always keep a manual backup of transactions.

## Support
For additional help or questions:
- Email: support@yourstore.com
- Phone: +234 800 123 4567
- Hours: Monday - Friday, 9:00 AM - 5:00 PM 