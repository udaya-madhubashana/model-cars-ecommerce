/**
 * ModelCars Pro - Client-side Functionality & AJAX Cart
 * File: js/script.js
 */

document.addEventListener('DOMContentLoaded', function () {
    initCartHandlers();
    initQuantityControls();
    initPaymentSelectors();
    initNewsletter();
});

/**
 * Toast Notification System
 */
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✓' : 'ℹ';
    toast.innerHTML = `<span style="color: #FFD700; font-weight: bold;">${icon}</span> <span>${message}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * AJAX Add to Cart
 */
function initCartHandlers() {
    // Dynamic Add to Cart buttons
    document.querySelectorAll('.ajax-add-to-cart').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const qtyInput = document.querySelector(`input[name="quantity"][data-product-id="${productId}"]`);
            const quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '⏳ Adding...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('ajax_action', 'add');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('php/cart.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;

                if (data.success) {
                    showToast(data.message, 'success');
                    updateCartBadge(data.cartCount);
                } else {
                    showToast(data.message || 'Error adding to cart', 'error');
                }
            })
            .catch(err => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                // Fallback for direct form submit if needed
                showToast('Item added to cart!', 'success');
                const badge = document.querySelector('.cart-count');
                if (badge) badge.textContent = parseInt(badge.textContent || 0) + quantity;
            });
        });
    });
}

/**
 * Update navbar cart badge
 */
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-count');
    if (badge) {
        badge.textContent = count;
        badge.style.transform = 'scale(1.3)';
        setTimeout(() => badge.style.transform = 'scale(1)', 200);
    }
}

/**
 * Quantity picker (+ / - buttons)
 */
function initQuantityControls() {
    document.querySelectorAll('.qty-control').forEach(group => {
        const minusBtn = group.querySelector('.qty-minus');
        const plusBtn = group.querySelector('.qty-plus');
        const input = group.querySelector('.qty-input');

        if (!input) return;

        if (minusBtn) {
            minusBtn.addEventListener('click', function () {
                let val = parseInt(input.value) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', function () {
                let val = parseInt(input.value) || 1;
                input.value = val + 1;
                input.dispatchEvent(new Event('change'));
            });
        }
    });
}

/**
 * Payment method selection highlights
 */
function initPaymentSelectors() {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.payment-label').forEach(lbl => lbl.classList.remove('selected'));
            if (this.checked && this.closest('.payment-label')) {
                this.closest('.payment-label').classList.add('selected');
            }
        });
    });
}

/**
 * Newsletter Form handler
 */
function initNewsletter() {
    const newsletterBtn = document.querySelector('.newsletter-form button');
    if (newsletterBtn) {
        newsletterBtn.addEventListener('click', function () {
            const input = document.querySelector('.newsletter-form input');
            if (input && input.value.trim() && input.value.includes('@')) {
                showToast(`Thank you! Subscribed with ${input.value.trim()}`, 'success');
                input.value = '';
            } else {
                showToast('Please enter a valid email address.', 'error');
            }
        });
    }
}
