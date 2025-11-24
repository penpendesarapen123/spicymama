function productsApp() {
    return {
        products: [], // Placeholder for JSON data
        carts: [],
        selectedProduct: {}, 
        filteredProducts: [], // Placeholder for JSON data

        // Function to show product details when a product is clicked
        showProductDetails(product) {
            // Update selectedProduct with the details of the clicked product
            this.selectedProduct = product;
            // Show the modal
            new bootstrap.Modal(document.getElementById('productDetailsModal')).show();
        },

        // Integrate the provided JavaScript script here
        payment: 0,
        init() {
            this.$watch('carts', () => {
                this.$refs.change.innerText = '00';
            });
        },
        validate(e) {
            let change = this.calculateChange();
            if (change < 0 || this.carts.length === 0) {
                e.preventDefault();
            }
        },
        calculateChange() {
            let change = this.payment - this.totalPrice;
            if (change < 0) {
                this.$refs.change.innerText = 'Not enough payment';
            } else {
                this.$refs.change.innerText = '₱' + change.toFixed(2);
            }
            return change;
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
            this.carts = [];
            this.payment = 0;
            this.calculateChange();
        },
        get totalPrice() {
            return this.carts.reduce((acm, cart) => acm + (cart.quantity * cart.product.price), 0);
        },
        subtractQuantity(cart) {
            cart.quantity--;
            if (cart.quantity < 1) {
                this.carts = this.carts.filter(_cart => _cart.product.id != cart.product.id);
            }
        },
        addQuantity(cart) {
            if (cart.quantity < cart.product.quantity) {
                cart.quantity++;
            }
        },
        addToCart(product) {
            const existingCartIndex = this.carts.findIndex(cart => cart.product.id === product.id);
            if (existingCartIndex !== -1) {
                // If the product is already in the cart, increase its quantity
                this.carts[existingCartIndex].quantity++;
            } else {
                // If the product is not in the cart, add it with quantity 1
                this.carts.push({
                    product: product,
                    quantity: 1,
                });
            }
            // Hide the modal after adding the product to the cart
            bootstrap.Modal.getInstance(document.getElementById('productDetailsModal')).hide();
        },

        filterCategory(category) {
            if (category === 'All') {
                this.filteredProducts = this.products;
            } else {
                this.filteredProducts = this.products.filter(product => product.category.name === category);
            }
        },

        async proccessOrder() {
            try {
                const formData = new FormData(document.getElementById('orderForm'));
                const response = await fetch('api/cashier_controller.php', {
                    method: 'POST',
                    body: formData,
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        // Optionally, you can handle a successful response here
                        console.log('Order processed successfully:', data);
                        // Clear the cart after successful order processing
                        this.carts = [];
                        // Show receipt with change
                        this.showReceipt(data.receipt, this.calculateChange());
                    } else {
                        // Handle failure response
                        console.error('Order processing failed:', data.error);
                        alert('Transaction failed!');
                    }
                } else {
                    // Handle HTTP error
                    console.error('HTTP Error:', response.status);
                    alert('An error occurred while processing the order.');
                }
            } catch (error) {
                // Handle network error
                console.error('Error processing order:', error);
                alert('An error occurred while processing the order.');
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
