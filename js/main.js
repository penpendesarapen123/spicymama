function addStock(productId) {
    // Set the product ID in the modal
    document.getElementById('productId').value = productId;

    // Show the modal using Bootstrap's JS API
    const addStockModal = new bootstrap.Modal(document.getElementById('addStockModal'));
    addStockModal.show();
}