# Tida Retail API Documentation

## Overview
The Tida Retail API provides a comprehensive set of endpoints for managing retail operations, including point-of-sale, inventory, payments, and analytics. The API follows RESTful principles and uses JSON for request and response bodies.

## Base URL
```
https://api.tidaretail.com/v1
```

## Authentication
All endpoints (except public routes) require authentication using Bearer tokens.

```http
Authorization: Bearer {token}
```

## Rate Limiting
- Standard endpoints: 60 requests per minute
- Payment endpoints: 30 requests per minute
- Admin endpoints: 20 requests per minute

## Versioning
The API is versioned using the URL path. The current version is v1.

## Common Response Format
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "meta": {
        // Pagination or other metadata
    }
}
```

## Error Responses
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Error message"]
    }
}
```

## API Sections

### 1. Authentication
- User registration and login
- Password reset
- Token management

### 2. Cart Management
- Create and manage shopping carts
- Add/remove items
- Apply discounts
- Generate receipts
- Void transactions

### 3. Payment Processing
- Bank transfers
- Moniepoint terminal payments
- Payment confirmation
- Receipt generation
- Payment history

### 4. Product Management
- Product CRUD operations
- Inventory management
- Stock movements
- Barcode lookup
- Low stock alerts
- Expiry tracking

### 5. Branch Management
- Branch operations
- Staff management
- Performance tracking
- Inventory by branch
- Sales by branch

### 6. Stock Transfers
- Transfer requests
- Approval workflow
- Item management
- Transfer history

### 7. Purchase Orders
- Order creation and management
- Approval workflow
- Status tracking
- Supplier management

### 8. Purchase Requisitions
- Requisition creation
- Approval process
- Status management
- Budget tracking

### 9. Reporting
- Sales reports
- Inventory reports
- Stock movement reports
- Payment reports
- Product reports
- Branch performance reports

### 10. Analytics
- Sales analytics
- Inventory analytics
- Product analytics
- Customer analytics
- Branch analytics

### 11. Admin Functions
- User management
- System settings
- Audit logs
- Role management

## Webhooks
The API supports webhooks for real-time notifications of:
- Payment status changes
- Stock level updates
- Order status changes
- System alerts

## SDKs
Official SDKs are available for:
- PHP
- JavaScript/Node.js
- Python
- Java
- .NET

## Best Practices
1. Always use HTTPS
2. Implement proper error handling
3. Cache responses when appropriate
4. Use pagination for large datasets
5. Implement retry logic for failed requests
6. Monitor rate limits
7. Keep API keys secure
8. Use webhooks for real-time updates

## Support
For API support:
- Email: support@tidaretail.com
- Documentation: https://docs.tidaretail.com
- Status Page: https://status.tidaretail.com

## Changelog
### v1.0.0 (2024-04-10)
- Initial API release
- Core POS functionality
- Inventory management
- Payment processing
- Reporting and analytics
- Admin functions 