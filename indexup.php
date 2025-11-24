<?php
// Guard
require_once '_guards.php'; 

$products = Product::all();
$categories = [];
$groupedProducts = [];

// Check if the user is logged in and retrieve their role
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

foreach ($products as $product) {
    $productName = $product->name;
    
    if (!isset($groupedProducts[$productName])) {
        // Initialize the grouped product with category and other general details
        $groupedProducts[$productName] = [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category->name, // Include the category here
            'image' => $product->image,
            'sizes' => []
        ];
    }
    
    $groupedProducts[$productName]['sizes'][] = [
        'id' => $product->id,  // Make sure size has its own ID here
        'size' => $product->size,
        'quantity' => $product->quantity,
        'price' => $product->price,
        'index' => count($groupedProducts[$productName]['sizes'])  // Track size index
    ];

    // Check if the category is already added
    $categoryExists = false;
    foreach ($categories as $cat) {
        if ($cat['id'] === $product->category->id) {
            $categoryExists = true;
            break;
        }
    }

    if (!$categoryExists) {
        $categories[] = [
            'id' => $product->category->id,
            'name' => $product->category->name
        ];
    }
}

// Sort categories alphabetically and place 'Other' (ID = 23) at the end
usort($categories, function ($a, $b) {
    if ($a['id'] === 23) return 1;  // Push "Other" to the end
    if ($b['id'] === 23) return -1;
    return strcmp($a['name'], $b['name']);  // Sort alphabetically
});

$groupedProducts = array_values($groupedProducts);
?>
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/cashier.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            overflow: hidden;
            background-color: #f9f9f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .products{
            overflow-y: auto; /* Enable vertical scrolling */
        }
        .product-item {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .product-item:hover {
            transform: scale(1.05);
        }
        .product-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }
        .product-item-container {
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
            background-color: white;
            border-radius: 8px;
        }
        .product-item-container:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .category-buttons {
            flex-shrink: 0; /* Prevent buttons from shrinking */
            padding: 8px;
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            background: #f9f9f9;
            box-shadow: inset 0 -1px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px){
            .category-buttons {
            flex-shrink: 0; /* Prevent buttons from shrinking */
            padding: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            box-shadow: inset 0 -1px rgba(0, 0, 0, 0.1);
        }
        }
        .category-buttons button {
            flex-grow: 1;
            padding: 10px;
            flex-basis: calc(25% - 10px);
            font-size: 14px;
            font-weight: bold;
            border-radius: 10px;
            background-color: #2E5378;
            color: white;
            transition: background-color 0.3s ease;
        }
        .category-buttons button:hover {
            background-color: #357ABD;
        }

        .modal .modal-content {
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        /* Cart Items */
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 10px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .cart-item-buttons button {
            border: none;
            width: 20px;
            height: 25px;
            color: #333;
            transition: background-color 0.2s ease;
        }
        .cart-item-buttons button:hover {
            background-color: #ccc;
        }
        /* Checkout Buttons */
        .btn-light { background-color: #DFF0D8; }
        .btn-warning { background-color: #F0AD4E; }
        .btn-danger { background-color: #D9534F; color: white; }
        .btn-info { background-color: #5BC0DE; }
        .btn-success { background-color: #5CB85C; }

        /* Updated button color scheme */
        .btn-primary { background-color: #0275D8; } /* Default action */
        .btn-dark { background-color: #292B2C; color: green; } /* Special for Paymaya */
        .btn-outline-danger { background-color: #F44336; color: white; }
        .btn-outline-primary { background-color: #007BFF; color: white; }

        /* Number buttons with color differentiation */
        .btn-light-green { background-color: #28a745; color: white; }
        .btn-light-blue { background-color: #17a2b8; color: white; }
        .btn-light-gold { background-color: #ffc107; color: white; }
        .btn-light-danger { background-color: #dc3545; color: white; }
        
        /* Subtle shadow for input fields */
        .form-control {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }
        
        /* Custom padding for key buttons */
        .input-group {
            margin-top: 15px;
        }

        /* Shadow for modals */
        .modal-dialog {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .sticky-cart-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
            display: none;
        }

        @media (max-width: 1367px) {
            .sticky-cart-button {
                display: block; /* Show sticky button on smaller screens */
            }
        }
        
    </style>
</head>

<?php require 'templates/admin_header.php' ?>

        <div class="flex grid">
            <?php require 'templates/admin_navbar.php' ?>
            <main x-data="productsApp()">
                <div x-data="cartHandler" class="main-container">
                <button class="btn btn-primary cart-button sticky-cart-button" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <i class="fa-solid fa-cart-shopping"></i>
                </button>
                    <div class="products forms">
                        <div class="subtitle">Items</div>
                        <hr/>

                        <?php displayFlashMessage('transaction') ?>

                        <div class="mb-4">
                            <input type="text" x-model="searchQuery" class="form-control" placeholder="Search items..." x-on:input="searchOnChange">
                        </div>

                        <div class="category-buttons" id="categoryButtons">
                            <button class="btn btn-secondary" @click="filterCategory('All')">All</button>
                            <?php foreach ($categories as $category) : ?>
                                <button class="btn btn-secondary" 
                                        :class="{'order-last': <?= $category['id'] ?> === 23}" 
                                        @click="filterCategory('<?= $category['name'] ?>')">
                                    <?= $category['name'] ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <template x-for="group in renderGroupedProducts()" :key="group.category">
                            <div>
                                <h3 class="subtitle mt-4" x-text="group.category"></h3>
                                <hr/>
                                <div class="grid" style="overflow-y: auto; flex-grow: 1;">
                                    <template x-for="product in group.products" :key="product.id">
                                        <div class="product-item-container border rounded p-3">
                                            <!-- Product item template -->
                                            <div class="product-item text-center" @click="showProductDetails(product)">
                                                <img :src="product.image" :alt="product.name">
                                                <div class="mt-2" x-text="product.name"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                <div class="cart-section forms"x-show="cartVisible" x-cloak>
                    <div>
                        <div class="subtitle">Cart</div>
    
                        <hr/>
                    </div>
                    <div class="cash-drawer">
                    <h5>Cash Drawer: ₱<span x-text="formatCash(cashDrawer)"></span></h5>
                    </div>
                    <?php if ($userRole === 'ADMIN'): ?>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cashDrawerModal">Open Cash Drawer</button>
                    <?php endif; ?>
                    <div id="cartItemsContainer" class="flex-grow" style="overflow-y: auto; height: 300px;">
                        <template x-for="cart in carts" :key="cart.product.id + '-' + cart.price">
                            <div class="cart-item">
                                <img :src="cart.product.image" alt="" class="cart-item-image" style="width: 50px; height: 50px; margin-right: 10px;">
                                <span class="left" x-text="cart.product.name + ' (' + cart.size + ')'"></span>
                                <div class="middle">
                                    <div class="cart-item-buttons">
                                        <button @click="subtractQuantity(cart)">-</button>
                                        <span x-text="cart.quantity"></span>
                                        <button @click="addQuantity(cart)">+</button>
                                    </div>
                                </div>
                                <span class="right" x-text="'₱' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(cart.quantity * cart.price)"></span>
                            </div>                                
                        </template>
                    </div>

                    <form id="orderForm" action="api/cashier_controller.php" method="POST" @submit.prevent="proccessOrder">
                        <input type="hidden" name="action" value="proccess_order">

                        <template x-for="(cart, i) in carts" :key="cart.product.id + '-' + cart.price">
                            <div>
                                <input type="hidden" :name="`cart_item[${i}][id]`" :value="cart.product.id">
                                <input type="hidden" :name="`cart_item[${i}][quantity]`" :value="cart.quantity">
                                <input type="hidden" :name="`cart_item[${i}][price]`" :value="cart.price">
                            </div>
                        </template>
                        
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <span>Total Price: </span>
                                <span class="font-bold" x-text="'₱' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPrice)"></span>
                            </div>
                            <div>
                                <span>Discount Applied: </span>
                                <span class="font-bold" x-text="discountAmount ? discountAmount + '%' : 'No Discount'"></span>
                             </div>
                             <button type="button" @click="clearCart" class="btn btn-outline-danger btn-sm">Clear Cart</button>
                        </div>


                        <div class="input-group mb-4">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Payment:</span>
                            </div>
                            <input type="number" class="form-control" aria-label="Amount (nearest)" x-model="payment" step="0.25" name="payment" class="w-full" x-on:input="calculateChange">
                        </div>

                        <div>
                            <div class="d-grid gap-2 mb-3" style="grid-template-columns: repeat(5, 1fr);">
                                <button  type="button" @click="addAmount(1)" class="btn btn-light">₱1</button>
                                <button  type="button" @click="addAmount(5)" class="btn btn-light">₱5</button>
                                <button  type="button" @click="addAmount(10)" class="btn btn-light">₱10</button>
                                <button  type="button" @click="addAmount(20)" class="btn btn-warning">₱20</button>
                                <button  type="button" @click="addAmount(50)" class="btn btn-danger">₱50</button>
                            </div>
                            
                            <div class="d-grid gap-2" style="grid-template-columns: repeat(5, 1fr);">
                                <button type="button" @click="addAmount(100)" class="btn btn-info">₱100</button>
                                <button type="button" @click="addAmount(200)" class="btn btn-success">₱200</button>
                                <button type="button" @click="addAmount(500)" class="btn btn-warning btn-gold">₱500</button>
                                <button type="button" @click="addAmount(1000)" class="btn btn-primary">₱1000</button>
                                <button type="button" @click="clearAmount()" class="btn btn-secondary">Clear</button>
                            </div>
                        </div>

                        <div class="mb-4"><hr/></div>
                            <div class="group mb-4 d-flex justify-content-between align-items-center">
                                <div>  
                                <p>Change:<span class="font-bold" x-ref="change"> --</span></p>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm" @click="applyDiscountModal()">Apply Discount</button>  
                        </div>
                          <!-- Checkout Warning Modal -->
                          <div class="modal fade" id="paymentWarningModal" tabindex="-1" aria-labelledby="paymentWarningModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="paymentWarningModalLabel">Payment Required</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Please enter a payment amount before checking out.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gcashModal">GCash</button>
                            <button type="button" class="btn btn-dark" style="color: green;" data-bs-toggle="modal" data-bs-target="#paymayaModal">Paymaya</button>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#bankCardModal">Bank Card</button>-->
                            <button type="submit" class="btn btn-success w-100" @click="proccessOrder">Checkout</button> 
                        </div>
                    </form>
                </div>

                <!-- Cart Modal -->
                    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="forms">
                                        <div class="cash-drawer">
                                            <h5>Cash Drawer: ₱<span x-text="formatCash(cashDrawer)"></span></h5>
                                        </div>
                                            <?php if ($userRole === 'ADMIN'): ?>
                                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#cashDrawerModal">Open Cash Drawer</button>
                                            <?php endif; ?>
                                            <div id="cartItemsContainer" class="flex-grow" style="overflow-y: auto; height: 300px;">
                                                <template x-for="cart in carts" :key="cart.product.id + '-' + cart.price">
                                                    <div class="cart-item">
                                                        <img :src="cart.product.image" alt="" class="cart-item-image" style="width: 50px; height: 50px; margin-right: 10px;">
                                                    <span class="left" x-text="cart.product.name + ' (' + cart.size + ')'"></span>
                                                    <div class="middle">
                                                        <div class="cart-item-buttons">
                                                            <button @click="subtractQuantity(cart)">-</button>
                                                            <span x-text="cart.quantity"></span>
                                                            <button @click="addQuantity(cart)">+</button>
                                                        </div>
                                                    </div>
                                                    <span class="right" x-text="'₱' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(cart.quantity * cart.price)"></span>
                                                </div>                                
                                            </template>
                                        </div>

                                        <form id="orderForm" action="api/cashier_controller.php" method="POST" @submit.prevent="proccessOrder">
                                            <input type="hidden" name="action" value="proccess_order">

                                            <template x-for="(cart, i) in carts" :key="cart.product.id + '-' + cart.price">
                                                <div>
                                                    <input type="hidden" :name="`cart_item[${i}][id]`" :value="cart.product.id">
                                                    <input type="hidden" :name="`cart_item[${i}][quantity]`" :value="cart.quantity">
                                                    <input type="hidden" :name="`cart_item[${i}][price]`" :value="cart.price">
                                                </div>
                                            </template>
                                            
                                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span>Total Price: </span>
                                                    <span class="font-bold" x-text="'₱' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPrice)"></span>
                                                </div>
                                                <div>
                                                    <span>Discount Applied: </span>
                                                    <span class="font-bold" x-text="discountAmount ? discountAmount + '%' : 'No Discount'"></span>
                                                </div>
                                                <button type="button" @click="clearCart" class="btn btn-outline-danger btn-sm">Clear Cart</button>
                                            </div>


                                            <div class="input-group mb-4 d-flex">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Payment:</span>
                                                </div>
                                                <input type="number" class="form-control" aria-label="Amount (nearest)" x-model="payment" step="0.25" name="payment" class="w-full" x-on:input="calculateChange">
                                            </div>

                                            <div>
                                                <div class="d-grid gap-2 mb-3" style="grid-template-columns: repeat(5, 1fr);">
                                                    <button  type="button" @click="addAmount(1)" class="btn btn-light">₱1</button>
                                                    <button  type="button" @click="addAmount(5)" class="btn btn-light">₱5</button>
                                                    <button  type="button" @click="addAmount(10)" class="btn btn-light">₱10</button>
                                                    <button  type="button" @click="addAmount(20)" class="btn btn-warning">₱20</button>
                                                    <button  type="button" @click="addAmount(50)" class="btn btn-danger">₱50</button>
                                                </div>
                                                
                                                <div class="d-grid gap-2" style="grid-template-columns: repeat(5, 1fr);">
                                                    <button type="button" @click="addAmount(100)" class="btn btn-info">₱100</button>
                                                    <button type="button" @click="addAmount(200)" class="btn btn-success">₱200</button>
                                                    <button type="button" @click="addAmount(500)" class="btn btn-warning btn-gold">₱500</button>
                                                    <button type="button" @click="addAmount(1000)" class="btn btn-primary">₱1000</button>
                                                    <button type="button" @click="clearAmount()" class="btn btn-secondary">Clear</button>
                                                </div>
                                            </div>

                                            <div class="mb-4"><hr/></div>
                                                <div class="group mb-4 d-flex justify-content-between align-items-center">
                                                    <div>  
                                                    <p>Change:<span class="font-bold" x-ref="change"> --</span></p>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="applyDiscountModal()">Apply Discount</button>  
                                            </div>
                                            <!-- Checkout Warning Modal -->
                                            <div class="modal fade" id="paymentWarningModal" tabindex="-1" aria-labelledby="paymentWarningModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="paymentWarningModalLabel">Payment Required</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Please enter a payment amount before checking out.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex gap-2">
                                                <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gcashModal">GCash</button>
                                                <button type="button" class="btn btn-dark" style="color: green;" data-bs-toggle="modal" data-bs-target="#paymayaModal">Paymaya</button>
                                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#bankCardModal">Bank Card</button>-->
                                                <button type="submit" class="btn btn-success w-100" @click="proccessOrder">Checkout</button> 
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        </div>

            <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <img :src="selectedProduct.image" alt="" style="width: 200px; height: 200px">
                                <h5 x-text="selectedProduct.name"></h5>
                            </div>
                            <div>
                                <p>Category: <span x-text="selectedProduct.category"></span></p>
                                <template x-if="selectedProduct.category !== 'Services'">
                                <div class="mb-3 d-flex align-items-center">
                                    <label for="size-select" class="form-label me-2">Size:</label>
                                    <select class="form-select form-select-sm" id="size-select" x-model="selectedSizeIndex"  @change="updateSelectedSizeDetails">
                                        <template x-for="(sizeOption, index) in selectedProduct.sizes" :key="index">
                                            <option :value="index" x-text="sizeOption.size"></option>
                                        </template>
                                    </select>
                                </div>
                                </template>
                                <template x-if="selectedProduct.category !== 'Services'">
                                <p>Quantity: <span x-text="selectedQuantity"></span></p>
                                </template>
                                <!-- Show price for all products except the service with id = 8 -->
                                <template x-if="selectedProduct.category !== 'Services'">
                                    <p>Price: ₱<span x-text="new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(selectedPrice)"></span></p>
                                </template>

                                <!-- Show custom price input only for the service with id = 8 -->
                                <template x-if="selectedProduct.category === 'Services'">
                                    <div class="mb-2 d-flex align-items-center">
                                        <label for="custom-price" class="form-label me-2">Price(₱):</label>
                                        <input class="form-control form-control-sm" type="number" id="custom-price" x-model="selectedPrice" min="250" step="1" />
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" @click="addToCart(selectedProduct)">
                                 Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Transaction Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <!-- Receipt content will be dynamically inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">Print</button>
            </div>
        </div>
    </div>
</div>


<?php require 'templates/discountmodal.php'; ?>


        <!-- Cash Drawer Modal -->
        <div class="modal fade" id="cashDrawerModal" tabindex="-1" aria-labelledby="cashDrawerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cashDrawerModalLabel">Cash Drawer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="initialCash">Enter Initial Cash Amount:</label>
                        <input type="number" id="initialCash" class="form-control" x-model="initialCash" placeholder="Enter cash amount">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="setCashDrawer">Set Cash Drawer</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>


<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/print-js"></script>



<script>
    function productsApp() {
    return {
        products: <?= json_encode($groupedProducts, JSON_PRETTY_PRINT) ?>, // Load grouped products
        searchQuery: '',
        carts: [],
        filteredProducts: [], // This will hold the products to be displayed
        selectedProduct: {
            id: null,
            name: '',
            category: '',
            image: '',
            sizes: []
        },
        selectedSizeIndex: 0,
        selectedQuantity: 0,
        selectedPrice: 0,
        payment: 0,
        discountAmount: 0,
        // Cash Drawer Variables
        initialCash: 0,
        cashDrawer: 0,  // Ensure cashDrawer is properly initialized

        // Initialize products and set initial cash drawer
        init() {
            this.filteredProducts = this.products; // Load all products initially
            this.loadCashDrawer(); // Call loadCashDrawer during initialization
        },

        // Fetch the current cash drawer balance from the backend
        loadCashDrawer() {
            fetch('api/cash_drawer_controller.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'get_balance' }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.cashDrawer = parseFloat(data.balance) || 0; // Properly assign the value
                    console.log('Cash drawer loaded:', this.cashDrawer); // Debug log
                } else {
                    alert('Failed to load cash drawer balance.');
                }
            })
            .catch(error => console.error('Error loading cash drawer:', error));
        },
        formatCash(amount) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        },

        // Admin sets the cash drawer balance (set_balance action)
        setCashDrawer() {
            fetch('api/cash_drawer_controller.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'set_balance',
                    new_balance: (this.initialCash),
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cash drawer balance set successfully.');
                    this.cashDrawer = parseFloat(this.initialCash);
                } else {
                    alert('Failed to set cash drawer balance.');
                }
            })
            .catch(error => console.error('Error setting cash drawer:', error));
        },

       // Update the cash drawer after a transaction (for managers and cashiers)
        updateCashDrawer(amount) {
            const updatedBalance = this.cashDrawer + amount;

            fetch('api/cash_drawer_controller.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'update_balance',
                    new_balance: updatedBalance,
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.cashDrawer = updatedBalance;
                    console.log('Cash drawer updated:', this.cashDrawer);
                } else {
                    alert('Failed to update cash drawer.');
                }
            })
            .catch(error => console.error('Error updating cash drawer:', error));
        },

        renderGroupedProducts() {
            // Get the current list of products (filtered or all)
            const products = this.filteredProducts || this.products;

            // Group products by category
            const groups = products.reduce((acc, product) => {
                const category = product.category || 'Uncategorized';
                if (!acc[category]) acc[category] = [];
                acc[category].push(product);
                return acc;
            }, {});

            // Define custom category order (ensure "Other" is last)
            const categoryOrder = Object.keys(groups).sort((a, b) => {
                if (a === 'Other') return 1;  // Push "Other" to the end
                if (b === 'Other') return -1;
                return a.localeCompare(b);  // Alphabetical order for the rest
            });

            // Convert grouped products into an array of { category, products }
            const groupedArray = categoryOrder.map(category => ({
                category,
                // Sort products alphabetically within each category
                products: groups[category].sort((a, b) => a.name.localeCompare(b.name)),
            }));

            return groupedArray;
        },

        // Filter products based on search query
        filterProducts() {
            if (!this.searchQuery.trim()) {
                this.filteredProducts = this.products;
                return;
            }
            const query = this.searchQuery.trim().toLowerCase();
            this.filteredProducts = this.products.filter(product =>
                product.name.toLowerCase().includes(query)
            );
        },
        

            // Function to be called when the search input changes
            searchOnChange() {
                this.filterProducts();
            },

            showProductDetails(product) {
                console.log("Selected Product:", product);  // Debug log to verify product data
                this.selectedProduct = product;
                this.selectedSizeIndex = 0; // Default to the first size option
                this.updateSelectedSizeDetails();
                new bootstrap.Modal(document.getElementById('productDetailsModal')).show();
            },

            updateSelectedSizeDetails() {
                const sizeDetails = this.selectedProduct.sizes[this.selectedSizeIndex];
                console.log("Size Details:", sizeDetails);  // Debug log to ensure size details are correct
                this.selectedPrice = sizeDetails.price;
                this.selectedQuantity = sizeDetails.quantity;
            },

            // Change selected size and update details accordingly
            onSizeChange(sizeIndex) {
                this.selectedSizeIndex = sizeIndex;
                this.updateSelectedSizeDetails();
            },

           
            applyDiscount(amount) {
                // Set the discount percentage
                this.discountAmount = amount;
                console.log("Discount applied:", amount); // Debug log
                this.calculateChange(); // Recalculate totals
            },

            clearDiscount() {
                // Reset the discount percentage
                this.discountAmount = 0;
                console.log("Discount cleared"); // Debug log
                this.calculateChange(); // Recalculate totals
            },

            range(start, end, step = 1) {
                // Generate an array of numbers between `start` and `end` with a step
                return Array.from({ length: Math.floor((end - start) / step) + 1 }, (_, i) => start + i * step);
            },


            validate(e) {
                let change = this.calculateChange();
                if (change < 0 || this.carts.length == 0) {
                    e.preventDefault();
                }
            },

            calculateChange() {
                const totalWithDiscount = this.totalPrice; // Ensure the discount is already applied here
                const change = this.payment - totalWithDiscount;

                if (change < 0) {
                    this.$refs.change.innerText = 'Not enough payment';
                    console.warn("Insufficient payment: Change is", change); // Debug log
                } else {
                    this.$refs.change.innerText = '₱' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(change);
                    console.log("Sufficient payment: Change is", change); // Debug log
                }
            },

            applyDiscountModal() {
                new bootstrap.Modal(document.getElementById('applyDiscountModal')).show();
            },

            addAmount(amount) {
                this.payment += amount;
                this.calculateChange();
            },

            clearAmount() {
                this.payment = 0;
                this.calculateChange();
            },

            clearCart() {
                this.carts.forEach(cart => {
                    const selectedSize = cart.product.sizes[this.selectedSizeIndex]; // Get the size
                    selectedSize.quantity += cart.quantity; // Replenish stock
                });
                this.carts = [];
                this.payment = 0;
                this.calculateChange();
                this.discountAmount = 0;
            },

            get totalPrice() {
                // Calculate total without discount
                const totalWithoutDiscount = this.carts.reduce(
                    (acc, cart) => acc + cart.quantity * cart.price,
                    0
                );
                // Apply discount factor
                const discountFactor = (100 - this.discountAmount) / 100;
                return totalWithoutDiscount * discountFactor;
            },

            subtractQuantity(cart) {
                const selectedSize = cart.product.sizes[this.selectedSizeIndex]; // Get selected size

                cart.quantity--; // Reduce the cart item quantity

                // Replenish stock when quantity decreases
                selectedSize.quantity++;

                // Remove the item from the cart if quantity drops below 1
                if (cart.quantity < 1) {
                    this.carts = this.carts.filter(_cart => _cart !== cart);
                }
            },

            addQuantity(cart) {
                const selectedSize = cart.product.sizes[this.selectedSizeIndex]; // Get selected size

                if (cart.product.category === 'Services') {
                    cart.quantity++; // Simply increase the quantity for services
                    return;
                }
                // Ensure enough stock is available
                if (selectedSize.quantity > 0) {
                    cart.quantity++;
                    selectedSize.quantity--; // Reduce stock
                } else {
                    alert("Not enough stock for this product size.");
                }
            },

            addToCart(product) {
                const selectedSize = product.sizes[this.selectedSizeIndex]; // Get selected size object
                console.log("Selected Size:", selectedSize);  // Debug log to ensure size details are correct
                const price = (product.category === 'Services' && this.selectedPrice > 0) 
                                ? this.selectedPrice 
                                : selectedSize.price;
                 // Check if a product with the same ID and price already exists in the cart
                 const existingCartItem = this.carts.find(
                    cartItem =>
                    cartItem.product.id === selectedSize.id &&
                    cartItem.size === selectedSize.size &&
                    cartItem.price === price
                );

                if (product.category !== 'Services') {// Check stock availability for products with size variations
                    if (selectedSize.quantity <= 0) {
                        alert("This product is out of stock for the selected size.");
                        return;
                    }
                }

                if (existingCartItem) {
                    // Increase the quantity if a matching item is found
                    existingCartItem.quantity++;
                } else {
                    // Add a new item to the cart if not found
                    this.carts.push({
                        product:  { ...product, id: selectedSize.id },
                        size: product.id === 8 ? 'N/A' : selectedSize.size,
                        sizeIndex: this.selectedSizeIndex, // Store the size index for future operations
                        price: price, // Store the price (custom or size-based)
                        quantity: 1,
                    });
                };

                // Reduce stock for the selected size
                selectedSize.quantity--;
               
                // Reset the selectedPrice after adding to cart
                this.selectedPrice = 0;  

                // Hide the modal after adding the product to the cart
                bootstrap.Modal.getInstance(document.getElementById('productDetailsModal')).hide();
            },

            filterCategory(category) {
                if (category === 'All') {
                    this.filteredProducts = this.products;
                } else {
                    this.filteredProducts = this.products.filter(product => product.category === category);
                }
            },

            async proccessOrder() {
    console.log("Cart Items Before Submission:", this.carts);
    event.preventDefault(); // Prevent form auto-submission
    if (this.payment >= this.totalPrice) {

        try {
            const formData = new FormData();
            formData.append('action', 'proccess_order');
            formData.append('payment', this.payment);

            // Add discount percentage to form data
            formData.append('discountPercentage', this.discountAmount);

            this.carts.forEach((item, index) => {
                console.log(`Cart Item ${index} ID:`, item.product.id);  // Log each product ID
                formData.append(`cart_item[${index}][id]`, item.product.id);
                formData.append(`cart_item[${index}][quantity]`, item.quantity);
                formData.append(`cart_item[${index}][price]`, item.price);
                const size = item.size || 'N/A';
                formData.append(`cart_item[${index}][size]`, size);
                formData.append(`cart_item[${index}][sizeIndex]`, item.sizeIndex); // Track size index for accuracy
            });

            const response = await fetch('api/cashier_controller.php', {
                method: 'POST',
                body: formData,
            });

            if (response.ok) {
                const data = await response.json();
                console.log("Response Data: ", data); // Log response data

                if (data.success) {
                    console.log('Order processed successfully:', data);
                    this.updateCashDrawer(this.totalPrice);  // Update the cash drawer after processing order
                    this.clearCart();         // Clear the cart after successful order
                    this.payment = 0;  // Reset payment here
                    this.totalPrice = 0; // Reset total price here
                    this.showReceipt(data.receipt, this.calculateChange());
                } else {
                    console.error('Order processing failed:', data.error);
                    alert('Transaction failed!');
                }
            } else {
                console.error('HTTP Error:', response.status);
                alert('Error');
            }
        } catch (error) {
            console.error('Error processing order:', error);
            alert('An error occurred while processing the order.');
        }
    } else {
        alert("Payment is less than the total price!");
    }
},

            showReceipt(receipt) {
                let receiptContent = document.getElementById('receiptContent');
                receiptContent.innerHTML = receipt;
                let transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
                transactionModal.show();
            }
        };
    }

    function printReceipt() {
        let receiptContent = document.getElementById('receiptContent').innerHTML;
        let originalContent = document.body.innerHTML;

        document.body.innerHTML = receiptContent;

        window.print();

        document.body.innerHTML = originalContent;

        window.location.reload(); // To ensure everything works fine after print
    }
    function printReceipt() {
        const receiptContent = `
            <style>
                @media print {
                    body {
                        margin: 0;
                        padding: 0;
                        font-family: Arial, sans-serif;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 0 auto;
                    }
                    table th, table td {
                        border: 1px solid #000;
                        padding: 5px;
                        text-align: left;
                    }
                    .receipt-header, .receipt-footer {
                        text-align: center;
                        margin-bottom: 10px;
                    }
                }
            </style>
            ${document.getElementById('receiptContent').innerHTML}
        `;

        printJS({
            printable: receiptContent,
            type: 'raw-html',
        });
    }

    document.addEventListener('alpine:init', () => {
    Alpine.data('cartHandler', () => ({
        cartVisible: true, // Cart is visible by default on larger screens
        isMobile: window.innerWidth <= 768,

        init() {
            // Update `isMobile` on window resize
            this.updateCartVisibility();

            window.addEventListener('resize', () => {
                this.updateCartVisibility();
            });
        },

        updateCartVisibility() {
            this.isMobile = window.innerWidth <= 768;

            if (this.isMobile) {
                this.cartVisible = false; // Hide cart on smaller screens
            } else {
                this.cartVisible = true; // Show cart on larger screens
            }
        },

        toggleCart() {
            if (this.isMobile) {
                this.cartVisible = !this.cartVisible; // Toggle cart visibility on mobile
            }
        }
    }));
});

// Modal Open Event
document.getElementById('cartModal').addEventListener('show.bs.modal', function () {
    console.log('Cart modal opened!');
    // Add any additional logic when the modal opens (e.g., analytics, content updates)
});

// Modal Close Event
document.getElementById('cartModal').addEventListener('hide.bs.modal', function () {
    console.log('Cart modal closed!');
    // Add any additional logic when the modal closes
});

window.addEventListener('resize', () => {
    const modal = document.getElementById('cartModal');

    // Check if the modal is currently open
    if (modal.classList.contains('show')) {
        // Use Bootstrap's modal instance to hide the modal
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    }
});

</script>


</body>
</html>