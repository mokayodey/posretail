# Inventory Management API Documentation

## Overview
This API documentation covers all aspects of inventory management in a retail environment. Whether you're a store manager, inventory clerk, or developer, this guide will help you understand how to manage products, track stock levels, and handle inventory operations efficiently.

### Key Features
- **Product Management**: Create, update, and track products
- **Stock Control**: Monitor and update stock levels
- **Multi-Location Support**: Manage inventory across multiple branches
- **Batch Tracking**: Track products by batch numbers
- **Expiry Management**: Monitor product expiry dates
- **Low Stock Alerts**: Get notified when stock is running low
- **Stock Movement History**: Track all inventory changes
- **Category Management**: Organize products into categories

## Getting Started

### Authentication
All API endpoints require authentication. Include your API token in the header:
```http
Authorization: Bearer your-api-token
```

### Base URL
All endpoints are relative to:
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

## Products

### Understanding Products
A product in the system represents any item you sell in your store. Each product has:
- Basic information (name, description, SKU)
- Pricing details (cost price, selling price)
- Stock information (quantity, location)
- Additional attributes (color, size, etc.)
- Images and documentation

### List Products
View all products with various filtering options.

```http
GET /api/products
```

#### Query Parameters Explained
- `category_id`: Show products in a specific category
- `branch_id`: Show products in a specific branch
- `low_stock`: Show only products with low stock
- `search`: Search by product name or SKU
- `sort`: Sort by name, price, or stock level
- `include`: Include related data (category, stock, prices)
- `status`: Filter by product status (active, inactive)
- `min_price`: Show products above this price
- `max_price`: Show products below this price
- `per_page`: Number of products per page

#### Example Request
```http
GET /api/products?category_id=1&branch_id=1&low_stock=true&search=shirt&sort=price&per_page=20
```

#### Response Explained
```json
{
    "success": true,
    "data": [
        {
            "id": 1,  // Unique product identifier
            "name": "Product A",  // Product name
            "sku": "PRD-001",  // Stock Keeping Unit
            "description": "Product description",  // Detailed description
            "price": 100.00,  // Selling price
            "cost_price": 80.00,  // Cost to the business
            "category": {
                "id": 1,
                "name": "Category A"  // Product category
            },
            "stock": {
                "quantity": 50,  // Current stock level
                "low_stock_threshold": 10,  // Alert when stock is below this
                "branch_id": 1,  // Branch location
                "location": "A1-01"  // Physical location in store
            },
            "pricing": {
                "base_price": 100.00,  // Regular price
                "sale_price": 90.00,  // Discounted price
                "wholesale_price": 85.00  // Bulk purchase price
            },
            "attributes": {
                "color": "Red",
                "size": "Large",
                "weight": "1kg"
            },
            "images": [
                {
                    "url": "https://api.tidaretail.com/images/1.jpg",
                    "is_primary": true  // Main product image
                }
            ],
            "status": "active",  // Product status
            "created_at": "2024-04-09T12:00:00.000000Z",
            "updated_at": "2024-04-09T12:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,  // Current page number
        "per_page": 20,  // Items per page
        "total": 100,  // Total number of products
        "links": {  // Navigation links
            "first": "https://api.tidaretail.com/products?page=1",
            "last": "https://api.tidaretail.com/products?page=5",
            "prev": null,
            "next": "https://api.tidaretail.com/products?page=2"
        }
    }
}
```

### Create Product
Add a new product to your inventory.

```http
POST /api/products
```

#### Request Body Explained
```json
{
    "name": "Product A",  // Product name
    "sku": "PRD-001",  // Unique stock keeping unit
    "description": "Product description",  // Detailed description
    "price": 100.00,  // Selling price
    "cost_price": 80.00,  // Cost to the business
    "category_id": 1,  // Product category
    "low_stock_threshold": 10,  // Alert when stock is below this
    "branch_id": 1,  // Branch location
    "location": "A1-01",  // Physical location in store
    "pricing": {
        "base_price": 100.00,  // Regular price
        "sale_price": 90.00,  // Discounted price
        "wholesale_price": 85.00  // Bulk purchase price
    },
    "attributes": {
        "color": "Red",  // Product color
        "size": "Large",  // Product size
        "weight": "1kg"  // Product weight
    },
    "images": [
        {
            "url": "https://api.tidaretail.com/images/1.jpg",
            "is_primary": true  // Main product image
        }
    ],
    "status": "active",  // Product status
    "tax_category": "standard",  // Tax category
    "barcode": "123456789012",  // Product barcode
    "unit_of_measure": "piece",  // Measurement unit
    "supplier_id": 1,  // Supplier information
    "reorder_point": 20,  // When to reorder
    "reorder_quantity": 50  // How much to reorder
}
```

#### Example Scenario
You're adding a new shirt to your inventory:
```json
{
    "name": "Men's Casual Shirt",
    "sku": "SHIRT-001",
    "description": "100% cotton casual shirt",
    "price": 5000.00,
    "cost_price": 3500.00,
    "category_id": 1,
    "low_stock_threshold": 5,
    "branch_id": 1,
    "location": "A1-01",
    "attributes": {
        "color": "Blue",
        "size": "XL",
        "material": "Cotton"
    },
    "barcode": "123456789012",
    "unit_of_measure": "piece",
    "reorder_point": 10,
    "reorder_quantity": 20
}
```

## Stock Management

### Understanding Stock Management
Stock management helps you:
- Track product quantities
- Monitor stock levels
- Record stock movements
- Manage batch numbers
- Track expiry dates
- Handle stock transfers

### Update Stock
Update the quantity of a product in stock.

```http
POST /api/products/{id}/stock
```

#### Request Body Explained
```json
{
    "quantity": 100,  // New stock quantity
    "branch_id": 1,  // Branch location
    "location": "A1-01",  // Physical location
    "batch_number": "BATCH-001",  // Batch identifier
    "expiry_date": "2024-12-31",  // Product expiry date
    "cost_price": 80.00,  // Cost per unit
    "notes": "Initial stock",  // Additional notes
    "type": "purchase",  // Type of stock movement
    "reference": "PO-123456"  // Reference number
}
```

#### Stock Movement Types
- `purchase`: New stock purchase
- `transfer`: Stock transfer between branches
- `adjustment`: Manual stock adjustment
- `return`: Customer return
- `damage`: Damaged stock
- `expiry`: Expired stock

#### Example Scenario
You're adding new stock from a purchase:
```json
{
    "quantity": 50,
    "branch_id": 1,
    "location": "A1-01",
    "batch_number": "BATCH-2024-001",
    "expiry_date": "2025-12-31",
    "cost_price": 3500.00,
    "notes": "New stock from supplier",
    "type": "purchase",
    "reference": "PO-2024-001"
}
```

### Stock Movement History
View the history of stock movements for a product.

```http
GET /api/products/{id}/movements
```

#### Query Parameters Explained
- `start_date`: Show movements from this date
- `end_date`: Show movements until this date
- `type`: Filter by movement type
- `branch_id`: Filter by branch
- `reference`: Filter by reference number

#### Example Request
```http
GET /api/products/1/movements?start_date=2024-01-01&end_date=2024-04-10&type=purchase
```

## Categories

### Understanding Categories
Categories help organize your products into logical groups. They can be:
- Hierarchical (parent-child relationships)
- Have multiple levels
- Include products from different branches
- Have specific attributes

### List Categories
View all product categories.

```http
GET /api/categories
```

#### Query Parameters Explained
- `parent_id`: Show subcategories of a specific category
- `include`: Include related data (products, subcategories)
- `status`: Filter by category status

#### Example Request
```http
GET /api/categories?parent_id=1&include=products,subcategories
```

## Best Practices

### For Store Managers
1. Regularly update stock levels
2. Monitor low stock alerts
3. Check expiry dates
4. Review stock movement reports
5. Maintain accurate product information

### For Inventory Clerks
1. Verify quantities during stock updates
2. Record accurate batch numbers
3. Update locations when moving stock
4. Document all stock movements
5. Report discrepancies immediately

### For Developers
1. Implement proper error handling
2. Use appropriate data types
3. Validate all inputs
4. Follow security best practices
5. Implement proper logging

## Frequently Asked Questions

### Q: How do I handle damaged stock?
A: Use the stock update endpoint with type "damage" and provide details in the notes.

### Q: What should I do if stock levels don't match?
A: Perform a stock adjustment and document the reason for the discrepancy.

### Q: How do I track products across multiple branches?
A: Use the branch_id parameter in stock-related endpoints to manage inventory per branch.

### Q: What happens when stock reaches the reorder point?
A: The system will generate a low stock alert and can optionally create a purchase order.

## Support
For additional help or questions:
- Email: support@yourstore.com
- Phone: +234 800 123 4567
- Hours: Monday - Friday, 9:00 AM - 5:00 PM 