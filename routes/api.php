<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\MoniepointController;
use App\Http\Controllers\SuregiftsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HealthCheckController;

// API Versioning
Route::prefix('v1')->group(function () {
    // Health Check
    Route::get('/health', [HealthCheckController::class, 'check']);
    Route::get('/metrics', [HealthCheckController::class, 'metrics']);

    // API Documentation
    Route::get('/docs', function () {
        return response()->json([
            'version' => '1.0.0',
            'endpoints' => [
                '/carts',
                '/payments',
                '/products',
                '/branches',
                '/stock-transfers',
                '/reports',
                '/analytics'
            ]
        ]);
    });

    // Public Routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Protected Routes
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        // Cart Management Routes
        Route::prefix('carts')->group(function () {
            Route::post('/', [CartController::class, 'create']);
            Route::get('/{cart}', [CartController::class, 'show']);
            Route::post('/{cart}/items', [CartController::class, 'addItem']);
            Route::put('/{cart}/items/{item}', [CartController::class, 'updateItem']);
            Route::delete('/{cart}/items/{item}', [CartController::class, 'removeItem']);
            Route::post('/{cart}/discount', [CartController::class, 'applyDiscount']);
            Route::post('/{cart}/void', [CartController::class, 'voidCart']);
            Route::get('/{cart}/receipt', [CartController::class, 'generateReceipt']);
        });

        // Payment Routes
        Route::prefix('payments')->group(function () {
            Route::post('/bank-transfer/{cart}', [PaymentController::class, 'processBankTransfer']);
            Route::post('/moniepoint/{cart}', [PaymentController::class, 'processMoniepointTransfer']);
            Route::post('/confirm/{payment}', [PaymentController::class, 'confirmTransferPayment']);
            Route::get('/pending-transfers', [PaymentController::class, 'getPendingTransfers']);
            Route::post('/{payment}/quick-confirm', [PaymentController::class, 'quickConfirmPayment']);
            Route::get('/cashier-pending', [PaymentController::class, 'getCashierPendingPayments']);
            Route::get('/{payment}/receipt', [PaymentController::class, 'generateReceipt']);
        });

        // Product Management Routes
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/{product}', [ProductController::class, 'show']);
            Route::put('/{product}', [ProductController::class, 'update']);
            Route::post('/{product}/stock', [ProductController::class, 'updateStock']);
            Route::get('/{product}/movements', [ProductController::class, 'getStockMovements']);
            Route::get('/barcode/{barcode}', [ProductController::class, 'getByBarcode']);
            Route::get('/low-stock', [ProductController::class, 'getLowStockProducts']);
            Route::get('/expiring', [ProductController::class, 'getExpiringProducts']);
        });

        // Purchase Order Routes
        Route::prefix('purchase-orders')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index']);
            Route::post('/', [PurchaseOrderController::class, 'store']);
            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
            Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update']);
            Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy']);
            Route::get('/statuses', [PurchaseOrderController::class, 'getStatuses']);
            Route::post('/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
            Route::post('/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject']);
        });

        // Purchase Requisition Routes
        Route::prefix('purchase-requisitions')->group(function () {
            Route::get('/', [PurchaseRequisitionController::class, 'index']);
            Route::post('/', [PurchaseRequisitionController::class, 'store']);
            Route::get('/{purchaseRequisition}', [PurchaseRequisitionController::class, 'show']);
            Route::put('/{purchaseRequisition}', [PurchaseRequisitionController::class, 'update']);
            Route::delete('/{purchaseRequisition}', [PurchaseRequisitionController::class, 'destroy']);
            Route::get('/statuses', [PurchaseRequisitionController::class, 'getStatuses']);
            Route::post('/{purchaseRequisition}/approve', [PurchaseRequisitionController::class, 'approve']);
            Route::post('/{purchaseRequisition}/reject', [PurchaseRequisitionController::class, 'reject']);
        });

        // Moniepoint Integration Routes
        Route::prefix('moniepoint')->group(function () {
            Route::post('/payments/initiate', [MoniepointController::class, 'initiatePayment']);
            Route::get('/payments/verify/{reference}', [MoniepointController::class, 'verifyPayment']);
            Route::get('/transactions', [MoniepointController::class, 'getTransactionHistory']);
            Route::post('/payments/{reference}/refund', [MoniepointController::class, 'refundPayment']);
            Route::post('/source-transfers', [MoniepointController::class, 'sourceMoniepointTransfers']);
            Route::get('/unmatched-transfers', [MoniepointController::class, 'getUnmatchedTransfers']);
            Route::get('/terminals', [MoniepointController::class, 'getTerminals']);
            Route::post('/terminals/{terminal}/assign', [MoniepointController::class, 'assignTerminal']);
        });

        // Suregifts Integration Routes
        Route::prefix('suregifts')->group(function () {
            Route::post('/gift-cards', [SuregiftsController::class, 'createGiftCard']);
            Route::get('/gift-cards/{giftCard}', [SuregiftsController::class, 'getGiftCard']);
            Route::post('/gift-cards/{giftCard}/redeem', [SuregiftsController::class, 'redeemGiftCard']);
            Route::get('/gift-cards', [SuregiftsController::class, 'listGiftCards']);
            Route::get('/gift-cards/{giftCard}/balance', [SuregiftsController::class, 'getGiftCardBalance']);
            Route::post('/gift-cards/{giftCard}/void', [SuregiftsController::class, 'voidGiftCard']);
            Route::get('/gift-cards/{giftCard}/transactions', [SuregiftsController::class, 'getGiftCardTransactions']);
        });

        // Branch Management Routes
        Route::prefix('branches')->group(function () {
            Route::get('/', [BranchController::class, 'index']);
            Route::post('/', [BranchController::class, 'store']);
            Route::get('/{branch}', [BranchController::class, 'show']);
            Route::put('/{branch}', [BranchController::class, 'update']);
            Route::delete('/{branch}', [BranchController::class, 'destroy']);
            Route::post('/{branch}/assign-users', [BranchController::class, 'assignUsers']);
            Route::get('/{branch}/inventory', [BranchController::class, 'getInventory']);
            Route::get('/{branch}/sales', [BranchController::class, 'getSales']);
            Route::get('/{branch}/staff', [BranchController::class, 'getStaff']);
            Route::get('/{branch}/performance', [BranchController::class, 'getPerformance']);
        });

        // Stock Transfer Routes
        Route::prefix('stock-transfers')->group(function () {
            Route::get('/', [StockTransferController::class, 'index']);
            Route::post('/', [StockTransferController::class, 'store']);
            Route::get('/{stockTransfer}', [StockTransferController::class, 'show']);
            Route::post('/{stockTransfer}/approve', [StockTransferController::class, 'approve']);
            Route::post('/{stockTransfer}/complete', [StockTransferController::class, 'complete']);
            Route::post('/{stockTransfer}/cancel', [StockTransferController::class, 'cancel']);
            Route::get('/{stockTransfer}/items', [StockTransferController::class, 'getTransferItems']);
            Route::post('/{stockTransfer}/items', [StockTransferController::class, 'addTransferItems']);
            Route::put('/{stockTransfer}/items/{item}', [StockTransferController::class, 'updateTransferItem']);
            Route::delete('/{stockTransfer}/items/{item}', [StockTransferController::class, 'removeTransferItem']);
        });

        // Reporting Routes
        Route::prefix('reports')->group(function () {
            Route::get('/sales', [ReportController::class, 'salesReport']);
            Route::get('/inventory', [ReportController::class, 'inventoryReport']);
            Route::get('/stock-movements', [ReportController::class, 'stockMovementsReport']);
            Route::get('/payments', [ReportController::class, 'paymentsReport']);
            Route::get('/products', [ReportController::class, 'productsReport']);
            Route::get('/branches', [ReportController::class, 'branchesReport']);
        });

        // Analytics Routes
        Route::prefix('analytics')->group(function () {
            Route::get('/sales', [AnalyticsController::class, 'salesAnalytics']);
            Route::get('/inventory', [AnalyticsController::class, 'inventoryAnalytics']);
            Route::get('/products', [AnalyticsController::class, 'productsAnalytics']);
            Route::get('/customers', [AnalyticsController::class, 'customersAnalytics']);
            Route::get('/branches', [AnalyticsController::class, 'branchesAnalytics']);
        });

        // Customer Loyalty Routes
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('/{customer}', [CustomerController::class, 'show']);
            Route::put('/{customer}', [CustomerController::class, 'update']);
            
            // Loyalty Points
            Route::post('/{customer}/points', [CustomerController::class, 'addPoints']);
            Route::post('/{customer}/points/redeem', [CustomerController::class, 'redeemPoints']);
            Route::get('/{customer}/points/history', [CustomerController::class, 'getLoyaltyHistory']);
            
            // Rewards
            Route::get('/{customer}/rewards', [CustomerController::class, 'getAvailableRewards']);
            Route::post('/{customer}/rewards', [CustomerController::class, 'createReward']);
            Route::post('/{customer}/rewards/{reward}/redeem', [CustomerController::class, 'redeemReward']);
        });
    });

    // Admin Routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AdminController::class, 'getUsers']);
            Route::post('/users', [AdminController::class, 'createUser']);
            Route::put('/users/{user}', [AdminController::class, 'updateUser']);
            Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
            Route::get('/audit-logs', [AdminController::class, 'getAuditLogs']);
            Route::get('/system-settings', [AdminController::class, 'getSystemSettings']);
            Route::put('/system-settings', [AdminController::class, 'updateSystemSettings']);
        });
    });
}); 