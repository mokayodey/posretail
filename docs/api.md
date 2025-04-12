# POS Retail API Documentation

The API is available at: `https://posretail-api.pipeops.app/api/v1`

> **Note**: During transition period, the old domain `https://api.tidaretail.com` is also supported.

## Authentication

All API requests require authentication using a Bearer token. Include the token in the Authorization header:

```bash
curl -H "Authorization: Bearer {token}" https://posretail-api.pipeops.app/api/v1/endpoint
```

## Endpoints

### Health Check
- **GET** `/health`
  - Checks the health status of the API
  - Returns 200 OK if healthy

### Authentication
- **POST** `/auth/login`
  - Authenticate user and get access token
  - Request body:
    ```json
    {
      "email": "user@example.com",
      "password": "password"
    }
    ```

## Table of Contents
1. [Authentication](#authentication)
2. [Products](#products)
3. [Purchase Requisitions](#purchase-requisitions)
4. [Purchase Orders](#purchase-orders)
5. [Moniepoint Integration](#moniepoint-integration)
6. [Suregifts Integration](#suregifts-integration)

## Authentication

### Login
```http
POST /api/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

### PIN Login
```http
POST /api/pin-login
```

**Request Body:**
```json
{
    "pin": "1234"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

### Logout
```http
POST /api/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

## Products

### List Products
```http
GET /api/products
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `search` (optional): Search term for name or barcode

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Product Name",
                "barcode": "123456789",
                "category": {
                    "id": 1,
                    "name": "Category Name"
                },
                "brand": {
                    "id": 1,
                    "name": "Brand Name"
                },
                "unit": {
                    "id": 1,
                    "name": "Unit Name"
                },
                "cost_price": "100.00",
                "selling_price": "150.00",
                "quantity": 50
            }
        ],
        "total": 100
    }
}
```

### Create Product
```http
POST /api/products
```

**Request Body:**
```json
{
    "name": "Product Name",
    "barcode": "123456789",
    "category_id": 1,
    "brand_id": 1,
    "unit_id": 1,
    "cost_price": 100.00,
    "selling_price": 150.00,
    "quantity": 50,
    "image": "base64_encoded_image",
    "description": "Product description"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Product Name",
        "barcode": "123456789",
        "category": {
            "id": 1,
            "name": "Category Name"
        },
        "brand": {
            "id": 1,
            "name": "Brand Name"
        },
        "unit": {
            "id": 1,
            "name": "Unit Name"
        },
        "cost_price": "100.00",
        "selling_price": "150.00",
        "quantity": 50
    }
}
```

## Purchase Requisitions

### List Purchase Requisitions
```http
GET /api/purchase-requisitions
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `status` (optional): Filter by status
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "reference_no": "PR-2024-001",
                "status": "pending",
                "total_amount": "1000.00",
                "requisition_lines": [
                    {
                        "product": {
                            "id": 1,
                            "name": "Product Name"
                        },
                        "quantity": 10,
                        "unit_price": "100.00",
                        "total": "1000.00"
                    }
                ]
            }
        ],
        "total": 50
    }
}
```

### Create Purchase Requisition
```http
POST /api/purchase-requisitions
```

**Request Body:**
```json
{
    "requisition_lines": [
        {
            "product_id": 1,
            "quantity": 10,
            "unit_price": 100.00
        }
    ],
    "notes": "Additional notes"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "reference_no": "PR-2024-001",
        "status": "pending",
        "total_amount": "1000.00",
        "requisition_lines": [
            {
                "product": {
                    "id": 1,
                    "name": "Product Name"
                },
                "quantity": 10,
                "unit_price": "100.00",
                "total": "1000.00"
            }
        ]
    }
}
```

## Purchase Orders

### List Purchase Orders
```http
GET /api/purchase-orders
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `status` (optional): Filter by status
- `supplier_id` (optional): Filter by supplier
- `location_id` (optional): Filter by location
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "reference_no": "PO-2024-001",
                "status": "ordered",
                "supplier": {
                    "id": 1,
                    "name": "Supplier Name"
                },
                "location": {
                    "id": 1,
                    "name": "Location Name"
                },
                "total_amount": "1000.00",
                "order_lines": [
                    {
                        "product": {
                            "id": 1,
                            "name": "Product Name"
                        },
                        "quantity": 10,
                        "unit_price": "100.00",
                        "total": "1000.00"
                    }
                ]
            }
        ],
        "total": 50
    }
}
```

### Create Purchase Order
```http
POST /api/purchase-orders
```

**Request Body:**
```json
{
    "supplier_id": 1,
    "location_id": 1,
    "order_lines": [
        {
            "product_id": 1,
            "quantity": 10,
            "unit_price": 100.00
        }
    ],
    "notes": "Additional notes"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "reference_no": "PO-2024-001",
        "status": "ordered",
        "supplier": {
            "id": 1,
            "name": "Supplier Name"
        },
        "location": {
            "id": 1,
            "name": "Location Name"
        },
        "total_amount": "1000.00",
        "order_lines": [
            {
                "product": {
                    "id": 1,
                    "name": "Product Name"
                },
                "quantity": 10,
                "unit_price": "100.00",
                "total": "1000.00"
            }
        ]
    }
}
```

## Moniepoint Integration

### Initiate Payment
```http
POST /api/moniepoint/payments/initiate
```

**Request Body:**
```json
{
    "amount": 1000.00,
    "currency": "NGN",
    "description": "Payment for order #123",
    "customer_email": "customer@example.com",
    "customer_name": "John Doe",
    "callback_url": "https://example.com/callback"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "reference": "MP-2024-001",
        "amount": "1000.00",
        "currency": "NGN",
        "status": "pending"
    }
}
```

### Verify Payment
```http
GET /api/moniepoint/payments/verify/{reference}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "reference": "MP-2024-001",
        "amount": "1000.00",
        "currency": "NGN",
        "status": "successful"
    }
}
```

### Get Transaction History
```http
GET /api/moniepoint/transactions
```

**Query Parameters:**
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date
- `status` (optional): Filter by status
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "reference": "MP-2024-001",
                "amount": "1000.00",
                "currency": "NGN",
                "status": "successful",
                "created_at": "2024-01-01 12:00:00"
            }
        ],
        "total": 50
    }
}
```

### Refund Payment
```http
POST /api/moniepoint/payments/{reference}/refund
```

**Request Body:**
```json
{
    "amount": 1000.00,
    "reason": "Customer requested refund"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "reference": "MP-2024-001",
        "amount": "1000.00",
        "currency": "NGN",
        "status": "refunded"
    }
}
```

## Suregifts Integration

### Create Gift Card
```http
POST /api/suregifts/gift-cards
```

**Request Body:**
```json
{
    "amount": 1000.00,
    "currency": "NGN",
    "quantity": 1,
    "type": "virtual",
    "expiry_date": "2024-12-31",
    "recipient_email": "recipient@example.com",
    "recipient_name": "John Doe",
    "message": "Happy Birthday!"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "card_id": "SG-2024-001",
        "amount": "1000.00",
        "currency": "NGN",
        "type": "virtual",
        "expiry_date": "2024-12-31",
        "status": "active"
    }
}
```

### Get Gift Card
```http
GET /api/suregifts/gift-cards/{cardId}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "card_id": "SG-2024-001",
        "amount": "1000.00",
        "currency": "NGN",
        "type": "virtual",
        "expiry_date": "2024-12-31",
        "status": "active",
        "balance": "1000.00"
    }
}
```

### Redeem Gift Card
```http
POST /api/suregifts/gift-cards/{cardId}/redeem
```

**Request Body:**
```json
{
    "amount": 500.00,
    "pin": "1234",
    "description": "Purchase of items"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "card_id": "SG-2024-001",
        "amount_redeemed": "500.00",
        "balance": "500.00",
        "status": "active"
    }
}
```

### List Gift Cards
```http
GET /api/suregifts/gift-cards
```

**Query Parameters:**
- `status` (optional): Filter by status
- `type` (optional): Filter by type
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date
- `page` (optional): Page number
- `per_page` (optional): Items per page

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "card_id": "SG-2024-001",
                "amount": "1000.00",
                "currency": "NGN",
                "type": "virtual",
                "expiry_date": "2024-12-31",
                "status": "active"
            }
        ],
        "total": 50
    }
}
```

### Get Gift Card Balance
```http
GET /api/suregifts/gift-cards/{cardId}/balance
```

**Response:**
```json
{
    "success": true,
    "data": {
        "card_id": "SG-2024-001",
        "balance": "500.00",
        "currency": "NGN"
    }
}
```

### Void Gift Card
```http
POST /api/suregifts/gift-cards/{cardId}/void
```

**Request Body:**
```json
{
    "reason": "Card lost or stolen"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "card_id": "SG-2024-001",
        "status": "voided"
    }
}
```

## Error Responses

All API endpoints may return the following error responses:

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": [
            "The field name is required."
        ]
    }
}
```

### Authentication Error (401)
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Authorization Error (403)
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error"
}
```

## Authentication

All endpoints except login and PIN login require authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {token}
```

## Rate Limiting

API requests are limited to:
- 60 requests per minute for authenticated users
- 30 requests per minute for unauthenticated users

## Pagination

All list endpoints support pagination with the following query parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

## Date Formats

All dates should be in ISO 8601 format:
```
YYYY-MM-DD
```

## Currency

All monetary values are in the smallest currency unit (e.g., cents for USD, kobo for NGN). 