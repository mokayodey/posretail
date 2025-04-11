# Multi-Location Management API Documentation

## Overview
This API documentation covers all aspects of managing multiple store locations in your retail business. Whether you're expanding to new locations, managing inventory across branches, or coordinating staff assignments, this guide will help you understand how to effectively manage multiple stores.

### Key Features
- **Branch Management**: Create and manage store locations
- **Stock Transfers**: Move inventory between branches
- **Staff Assignment**: Manage staff across locations
- **Inventory Tracking**: Track stock levels per branch
- **Performance Analysis**: Compare branch performance
- **Operating Hours**: Manage branch schedules
- **Contact Details**: Maintain branch information
- **Access Control**: Manage branch-level permissions

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

## Branch Management

### Understanding Branches
A branch in the system represents a physical store location. Each branch has:
- Basic information (name, address)
- Contact details (phone, email)
- Operating hours
- Staff assignments
- Inventory levels
- Performance metrics

### List All Branches
View all your store locations.

```http
GET /api/branches
```

#### Example Request
```http
GET /api/branches?status=active&include=users,inventory
```

#### Response Explained
```json
{
    "success": true,
    "data": [
        {
            "id": 1,  // Unique branch identifier
            "name": "Main Branch",  // Branch name
            "address": "123 Main St",  // Physical location
            "phone_number": "08012345678",  // Contact number
            "email": "main@example.com",  // Branch email
            "operating_hours": "9:00 AM - 9:00 PM",  // Business hours
            "branch_code": "MB001",  // Unique branch code
            "is_active": true,  // Branch status
            "created_at": "2024-04-09T12:00:00.000000Z",
            "updated_at": "2024-04-09T12:00:00.000000Z",
            "users": [  // Assigned staff
                {
                    "id": 1,
                    "name": "John Doe",
                    "email": "john@example.com",
                    "pivot": {
                        "is_primary": true  // Primary work location
                    }
                }
            ]
        }
    ]
}
```

### Create New Branch
Add a new store location to your business.

```http
POST /api/branches
```

#### Request Body Explained
```json
{
    "name": "Main Branch",  // Branch name
    "address": "123 Main St",  // Physical address
    "phone_number": "08012345678",  // Contact number
    "email": "main@example.com",  // Branch email
    "operating_hours": "9:00 AM - 9:00 PM",  // Business hours
    "branch_code": "MB001"  // Unique identifier
}
```

#### Example Scenario
Opening a new branch in a different location:
```json
{
    "name": "Victoria Island Branch",
    "address": "15 Adeola Odeku Street, VI, Lagos",
    "phone_number": "08012345678",
    "email": "vi@yourstore.com",
    "operating_hours": "8:00 AM - 10:00 PM",
    "branch_code": "VI001"
}
```

### Update Branch
Modify existing branch information.

```http
PUT /api/branches/{id}
```

#### Request Body Explained
```json
{
    "name": "Updated Branch Name",  // New branch name
    "address": "456 New St",  // New address
    "phone_number": "08098765432",  // Updated phone
    "email": "updated@example.com",  // New email
    "operating_hours": "8:00 AM - 10:00 PM",  // New hours
    "is_active": true  // Branch status
}
```

## Staff Management

### Assign Users to Branch
Assign staff members to specific branches.

```http
POST /api/branches/{id}/assign-users
```

#### Request Body Explained
```json
{
    "users": [
        {
            "user_id": 1,  // Staff member ID
            "is_primary": true  // Primary work location
        },
        {
            "user_id": 2,  // Another staff member
            "is_primary": false  // Secondary location
        }
    ]
}
```

#### Example Scenario
Assigning a manager and cashiers to a new branch:
```json
{
    "users": [
        {
            "user_id": 1,  // Branch manager
            "is_primary": true
        },
        {
            "user_id": 2,  // Cashier 1
            "is_primary": true
        },
        {
            "user_id": 3,  // Cashier 2
            "is_primary": true
        }
    ]
}
```

## Stock Transfers

### Understanding Stock Transfers
Stock transfers allow you to:
- Move inventory between branches
- Track stock movement
- Maintain accurate inventory levels
- Handle branch stock requests
- Monitor transfer status

### List Stock Transfers
View all stock transfers between branches.

```http
GET /api/stock-transfers
```

#### Query Parameters Explained
- `status`: Transfer status (pending, approved, in_transit, completed)
- `branch_id`: Source or destination branch
- `date_range`: Filter by date range
- `product_id`: Filter by specific product

#### Example Request
```http
GET /api/stock-transfers?status=pending&branch_id=1
```

#### Response Explained
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "transfer_code": "TR-ABC123",  // Unique transfer reference
            "status": "pending",  // Current status
            "source_branch": {
                "id": 1,
                "name": "Main Branch"  // Sending branch
            },
            "destination_branch": {
                "id": 2,
                "name": "Branch 2"  // Receiving branch
            },
            "items": [
                {
                    "id": 1,
                    "product": {
                        "id": 1,
                        "name": "Product A"
                    },
                    "quantity": 10,  // Transfer quantity
                    "unit_cost": 100.00  // Cost per unit
                }
            ],
            "created_by": {
                "id": 1,
                "name": "John Doe"  // Transfer initiator
            },
            "approved_by": null,  // Approval details
            "approved_at": null,
            "completed_at": null
        }
    ]
}
```

### Create Stock Transfer
Initiate a stock transfer between branches.

```http
POST /api/stock-transfers
```

#### Request Body Explained
```json
{
    "source_branch_id": 1,  // Sending branch
    "destination_branch_id": 2,  // Receiving branch
    "items": [
        {
            "product_id": 1,  // Product to transfer
            "quantity": 10,  // Transfer quantity
            "notes": "Urgent stock request"  // Additional information
        }
    ],
    "transfer_date": "2024-04-10",  // Planned transfer date
    "notes": "Monthly stock rebalancing"  // Transfer purpose
}
```

## Best Practices

### For Branch Managers
1. Regularly update branch information
2. Monitor stock levels
3. Process transfers promptly
4. Maintain staff assignments
5. Track branch performance

### For Stock Controllers
1. Verify stock before transfer
2. Document all movements
3. Update transfer status
4. Check receiving branch capacity
5. Maintain transfer records

### For System Administrators
1. Set up proper access controls
2. Monitor branch activities
3. Maintain branch configurations
4. Handle staff reassignments
5. Review system logs

## Frequently Asked Questions

### Q: How do I handle emergency stock transfers?
A: Use the stock transfer endpoint with priority flag and urgent notes.

### Q: What happens when a branch becomes inactive?
A: The branch's operations are suspended, but historical data is preserved.

### Q: How do I track staff working at multiple branches?
A: Use the staff assignment endpoint with primary/secondary location flags.

### Q: Can I transfer stock between multiple branches at once?
A: Create separate transfer requests for each branch pair.

## Support
For additional help or questions:
- Email: support@yourstore.com
- Phone: +234 800 123 4567
- Hours: Monday - Friday, 9:00 AM - 5:00 PM 