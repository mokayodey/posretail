# Reporting and Analytics API Documentation

## Overview
The Reporting and Analytics API helps you understand your business performance. You can get sales reports, inventory insights, and customer analytics to make better business decisions.

## Getting Started
Before using reports, you'll need:
1. A valid API token
2. Access to the data you want to analyze
3. Date range for your report

## Base Endpoints
```
GET    /v1/reports/sales           # Sales reports
GET    /v1/reports/inventory       # Inventory reports
GET    /v1/reports/customers       # Customer analytics
GET    /v1/reports/products        # Product performance
GET    /v1/reports/branches        # Branch performance
GET    /v1/reports/staff           # Staff performance
```

## Sales Reports
Get detailed sales information.

```http
GET /v1/reports/sales
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| start_date | date | Yes | Start date | "2024-01-01" |
| end_date | date | Yes | End date | "2024-01-31" |
| branch_id | number | No | Filter by branch | 1 |
| payment_method | string | No | Filter by payment | "cash" |
| group_by | string | No | Group results | "day" |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_sales": 100000.00,
        "total_orders": 500,
        "average_order_value": 200.00,
        "sales_by_day": [
            {
                "date": "2024-01-01",
                "sales": 5000.00,
                "orders": 25
            }
        ],
        "sales_by_payment_method": [
            {
                "method": "cash",
                "amount": 60000.00,
                "percentage": 60
            }
        ]
    }
}
```

## Inventory Reports
Get stock level insights.

```http
GET /v1/reports/inventory
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| branch_id | number | No | Filter by branch | 1 |
| category_id | number | No | Filter by category | 1 |
| low_stock | boolean | No | Show low stock | true |
| expiring | boolean | No | Show expiring | true |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_products": 100,
        "total_value": 50000.00,
        "low_stock_items": [
            {
                "product_id": 1,
                "name": "Product A",
                "current_stock": 5,
                "minimum_stock": 10
            }
        ],
        "expiring_items": [
            {
                "product_id": 2,
                "name": "Product B",
                "expiry_date": "2024-02-01",
                "quantity": 20
            }
        ]
    }
}
```

## Customer Analytics
Get customer behavior insights.

```http
GET /v1/reports/customers
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| start_date | date | Yes | Start date | "2024-01-01" |
| end_date | date | Yes | End date | "2024-01-31" |
| branch_id | number | No | Filter by branch | 1 |
| group_by | string | No | Group results | "month" |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_customers": 1000,
        "new_customers": 100,
        "repeat_customers": 900,
        "average_spend": 500.00,
        "top_customers": [
            {
                "customer_id": 1,
                "name": "John Doe",
                "total_spent": 10000.00,
                "orders": 50
            }
        ]
    }
}
```

## Product Performance
Get product sales and performance data.

```http
GET /v1/reports/products
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| start_date | date | Yes | Start date | "2024-01-01" |
| end_date | date | Yes | End date | "2024-01-31" |
| branch_id | number | No | Filter by branch | 1 |
| category_id | number | No | Filter by category | 1 |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_products": 50,
        "top_selling": [
            {
                "product_id": 1,
                "name": "Product A",
                "quantity_sold": 100,
                "revenue": 10000.00
            }
        ],
        "slow_moving": [
            {
                "product_id": 2,
                "name": "Product B",
                "quantity_sold": 5,
                "revenue": 500.00
            }
        ]
    }
}
```

## Branch Performance
Get branch-specific performance data.

```http
GET /v1/reports/branches
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| start_date | date | Yes | Start date | "2024-01-01" |
| end_date | date | Yes | End date | "2024-01-31" |
| branch_id | number | No | Filter by branch | 1 |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_branches": 5,
        "total_sales": 500000.00,
        "branch_performance": [
            {
                "branch_id": 1,
                "name": "Main Store",
                "sales": 200000.00,
                "orders": 1000,
                "growth": 15
            }
        ]
    }
}
```

## Staff Performance
Get staff productivity and sales data.

```http
GET /v1/reports/staff
```

### Query Parameters
| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| start_date | date | Yes | Start date | "2024-01-01" |
| end_date | date | Yes | End date | "2024-01-31" |
| branch_id | number | No | Filter by branch | 1 |
| role | string | No | Filter by role | "cashier" |

### Example Response
```json
{
    "success": true,
    "data": {
        "total_staff": 20,
        "top_performers": [
            {
                "staff_id": 1,
                "name": "John Doe",
                "role": "cashier",
                "sales": 50000.00,
                "orders": 250,
                "average_order_value": 200.00
            }
        ]
    }
}
```

## Common Errors

### 400 Bad Request
```json
{
    "success": false,
    "message": "Invalid report parameters",
    "errors": {
        "start_date": ["Start date is required"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Report not found"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "Failed to generate report"
}
```

## Tips for Success
1. Use appropriate date ranges for analysis
2. Filter data by relevant parameters
3. Export reports for offline analysis
4. Monitor key performance indicators
5. Compare performance across periods
6. Use data to make business decisions
7. Keep reports organized
8. Share insights with team members

## Need Help?
If you're having trouble:
1. Check the error message
2. Verify your date ranges
3. Make sure you have proper access
4. Contact support if the issue persists 