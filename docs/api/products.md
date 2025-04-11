# Products API Documentation

## Overview
The Products API provides endpoints for managing products, inventory, and stock movements in the Tida Retail system. It supports multi-location inventory management, barcode scanning, and comprehensive stock tracking.

## Base Endpoints
```
GET    /v1/products              # List products
POST   /v1/products              # Create product
GET    /v1/products/{product}    # Get product details
PUT    /v1/products/{product}    # Update product
POST   /v1/products/{product}/stock  # Update stock
GET    /v1/products/{product}/movements  # Get stock movements
GET    /v1/products/barcode/{barcode}  # Get product by barcode
GET    /v1/products/low-stock    # Get low stock products
GET    /v1/products/expiring     # Get expiring products
```

## List Products
Retrieve a list of products with various filtering options.

```http
GET /v1/products
```

### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| category_id | integer | Filter by category ID |
| branch_id | integer | Filter by branch ID |
| low_stock | boolean | Filter products with low stock |
| search | string | Search by name, SKU, or barcode |
| sort | string | Sort by field (e.g., 'name', 'price') |
| status | string | Filter by status (active/inactive) |
| min_price | number | Filter by minimum price |
| max_price | number | Filter by maximum price |
| per_page | integer | Items per page (default: 20) |
| page | integer | Page number (default: 1) |

### Example Request
```http
GET /v1/products?category_id=1&low_stock=true&search=shirt&sort=price&status=active&min_price=10&max_price=100
```

### Example Response
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Blue Shirt",
                "sku": "SHIRT001",
                "barcode": "123456789",
                "description": "A comfortable blue shirt",
                "price": 29.99,
                "cost_price": 15.00,
                "category_id": 1,
                "low_stock_threshold": 10,
                "status": "active",
                "created_at": "2024-01-01T00:00:00.000000Z",
                "updated_at": "2024-01-01T00:00:00.000000Z",
                "category": {
                    "id": 1,
                    "name": "Shirts"
                },
                "inventory": {
                    "quantity": 5,
                    "low_stock_threshold": 10
                }
            }
        ],
        "total": 1,
        "per_page": 20
    }
}
```

## Create Product
Create a new product with initial inventory.

```http
POST /v1/products
```

### Request Body
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Product name |
| sku | string | Yes | Unique SKU |
| barcode | string | No | Product barcode |
| description | string | No | Product description |
| price | number | Yes | Selling price |
| cost_price | number | Yes | Cost price |
| category_id | integer | Yes | Category ID |
| supplier_id | integer | No | Supplier ID |
| branch_id | integer | Yes | Branch ID for initial inventory |
| low_stock_threshold | integer | Yes | Low stock alert threshold |
| reorder_point | integer | Yes | Reorder point quantity |
| location | string | No | Storage location |
| attributes | object | No | Product attributes |
| images | array | No | Product images |
| status | string | Yes | Product status (active/inactive) |
| tax_category | string | No | Tax category |
| unit_of_measure | string | Yes | Unit of measure |
| reorder_quantity | integer | Yes | Reorder quantity |

### Example Request
```json
{
    "name": "Blue Shirt",
    "sku": "SHIRT001",
    "barcode": "123456789",
    "description": "A comfortable blue shirt",
    "price": 29.99,
    "cost_price": 15.00,
    "category_id": 1,
    "supplier_id": 1,
    "branch_id": 1,
    "low_stock_threshold": 10,
    "reorder_point": 5,
    "location": "A1",
    "attributes": {
        "color": "blue",
        "size": "M"
    },
    "images": [
        "image1.jpg",
        "image2.jpg"
    ],
    "status": "active",
    "tax_category": "clothing",
    "unit_of_measure": "piece",
    "reorder_quantity": 20
}
```

## Update Stock
Update product stock quantity and record movement.

```http
POST /v1/products/{product}/stock
```

### Request Body
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| quantity | integer | Yes | Quantity to add/remove |
| branch_id | integer | Yes | Branch ID |
| location | string | No | Storage location |
| batch_number | string | No | Batch number |
| expiry_date | date | No | Expiry date |
| cost_price | number | No | Cost price |
| notes | string | No | Movement notes |
| type | string | Yes | Movement type (purchase/transfer/adjustment/return/damage/expiry) |
| reference | string | No | Reference number |
| barcode | string | No | Product barcode (alternative to product ID) |

### Example Request
```json
{
    "quantity": 10,
    "branch_id": 1,
    "location": "A1",
    "batch_number": "BATCH001",
    "expiry_date": "2024-12-31",
    "cost_price": 15.00,
    "notes": "Initial stock",
    "type": "purchase",
    "reference": "PO123"
}
```

## Get Stock Movements
Retrieve stock movement history for a product.

```http
GET /v1/products/{product}/movements
```

### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| branch_id | integer | Branch ID |
| start_date | date | Start date for filtering |
| end_date | date | End date for filtering |
| type | string | Movement type filter |
| reference | string | Reference number filter |
| per_page | integer | Items per page (default: 20) |
| page | integer | Page number (default: 1) |

### Example Request
```http
GET /v1/products/1/movements?branch_id=1&start_date=2024-01-01&end_date=2024-01-31&type=purchase
```

## Get Product by Barcode
Retrieve a product using its barcode.

```http
GET /v1/products/barcode/{barcode}
```

### Example Response
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Blue Shirt",
        "sku": "SHIRT001",
        "barcode": "123456789",
        "price": 29.99,
        "inventory": {
            "quantity": 5,
            "location": "A1"
        }
    }
}
```

## Get Low Stock Products
Retrieve products with stock levels below their threshold.

```http
GET /v1/products/low-stock
```

### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| branch_id | integer | Branch ID |
| category_id | integer | Category ID |
| threshold | integer | Custom threshold (optional) |

## Get Expiring Products
Retrieve products that are approaching their expiry date.

```http
GET /v1/products/expiring
```

### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| branch_id | integer | Branch ID |
| days | integer | Days until expiry (default: 30) |
| category_id | integer | Category ID |

## Error Responses

### 400 Bad Request
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field": ["Error message"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Product not found"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Failed to process request",
    "error": "Error message"
}
```

## Best Practices
1. Always validate barcodes before creating products
2. Use batch operations for bulk stock updates
3. Implement proper error handling
4. Cache frequently accessed product data
5. Monitor stock levels and set appropriate thresholds
6. Use webhooks for real-time stock updates
7. Implement proper logging for stock movements
8. Use transactions for critical operations 