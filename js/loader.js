/**
 * Show the page loader
 */
function showLoader() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.classList.add('active');
    }
}

/**
 * Hide the page loader
 */
function hideLoader() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.classList.remove('active');
    }
}
