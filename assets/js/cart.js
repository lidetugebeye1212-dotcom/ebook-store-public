// Cart Management JavaScript

class CartManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupQuantityControls();
        this.setupRemoveButtons();
        this.setupAddToCartButtons();
        this.updateCartCount();
    }
    
    setupQuantityControls() {
        const quantityInputs = document.querySelectorAll('.cart-quantity input');
        quantityInputs.forEach(input => {
            input.addEventListener('change', (e) => this.updateQuantity(e.target));
        });
        
        const incrementButtons = document.querySelectorAll('.quantity-increment');
        incrementButtons.forEach(button => {
            button.addEventListener('click', (e) => this.incrementQuantity(e.target));
        });
        
        const decrementButtons = document.querySelectorAll('.quantity-decrement');
        decrementButtons.forEach(button => {
            button.addEventListener('click', (e) => this.decrementQuantity(e.target));
        });
    }
    
    setupRemoveButtons() {
        const removeButtons = document.querySelectorAll('.remove-item');
        removeButtons.forEach(button => {
            button.addEventListener('click', (e) => this.removeItem(e.target));
        });
    }
    
    setupAddToCartButtons() {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const bookId = button.dataset.bookId;
                this.addToCart(bookId, button);
            });
        });
    }
    
    async addToCart(bookId, button) {
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            const response = await fetch('../user/ajax/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `book_id=${bookId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Book added to cart successfully!', 'success');
                this.updateCartCount(data.cartCount);
                
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    button.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to add to cart');
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            button.disabled = false;
        }
    }
    
    async updateQuantity(input) {
        const cartId = input.dataset.cartId;
        const quantity = parseInt(input.value);
        
        if (quantity < 1) {
            this.removeItem({ dataset: { cartId } });
            return;
        }
        
        try {
            const response = await fetch('../user/ajax/update-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}&quantity=${quantity}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateCartItemTotal(cartId, quantity);
                this.updateCartTotal();
                this.showNotification('Cart updated successfully!', 'success');
            }
        } catch (error) {
            this.showNotification('Failed to update cart', 'error');
        }
    }
    
    async removeItem(element) {
        const cartId = element.dataset.cartId;
        
        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return;
        }
        
        try {
            const response = await fetch('../user/ajax/remove-from-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                const cartItem = document.getElementById(`cart-item-${cartId}`);
                if (cartItem) {
                    cartItem.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        cartItem.remove();
                        this.updateCartTotal();
                        this.checkEmptyCart();
                    }, 300);
                }
                
                this.updateCartCount(data.cartCount);
                this.showNotification('Item removed from cart', 'success');
            }
        } catch (error) {
            this.showNotification('Failed to remove item', 'error');
        }
    }
    
    incrementQuantity(button) {
        const input = button.parentElement.querySelector('input');
        const currentValue = parseInt(input.value);
        if (currentValue < 10) {
            input.value = currentValue + 1;
            this.updateQuantity(input);
        }
    }
    
    decrementQuantity(button) {
        const input = button.parentElement.querySelector('input');
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
            this.updateQuantity(input);
        }
    }
    
    updateCartItemTotal(cartId, quantity) {
        const priceElement = document.querySelector(`#cart-item-${cartId} .item-price`);
        const basePrice = parseFloat(priceElement.dataset.basePrice);
        const itemTotalElement = document.querySelector(`#cart-item-${cartId} .item-total`);
        
        if (itemTotalElement) {
            itemTotalElement.textContent = `$${(basePrice * quantity).toFixed(2)}`;
        }
    }
    
    updateCartTotal() {
        const itemTotals = document.querySelectorAll('.item-total');
        let total = 0;
        
        itemTotals.forEach(element => {
            total += parseFloat(element.textContent.replace('$', ''));
        });
        
        const cartTotalElement = document.querySelector('.cart-total-amount');
        if (cartTotalElement) {
            cartTotalElement.textContent = `$${total.toFixed(2)}`;
        }
    }
    
    updateCartCount(count) {
        const cartLink = document.querySelector('.nav-links a[href*="cart"]');
        if (cartLink) {
            if (count > 0) {
                cartLink.innerHTML = `<i class="fas fa-shopping-cart"></i> Cart (${count})`;
            } else {
                cartLink.innerHTML = '<i class="fas fa-shopping-cart"></i> Cart';
            }
        }
    }
    
    checkEmptyCart() {
        const cartItems = document.querySelectorAll('.cart-item');
        const cartContainer = document.querySelector('.cart-container');
        
        if (cartItems.length === 0) {
            cartContainer.innerHTML = `
                <div style="text-align: center; padding: 4rem;">
                    <span style="font-size: 5rem;">🛒</span>
                    <h2 style="margin: 2rem 0;">Your cart is empty</h2>
                    <p style="color: var(--gray); margin-bottom: 2rem;">Looks like you haven't added any Ethiopian books to your cart yet.</p>
                    <a href="../public/index.php" class="btn btn-primary">Start Shopping</a>
                </div>
            `;
        }
    }
    
    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#00b894' : '#d63031'};
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            z-index: 9999;
            animation: slideInRight 0.3s ease;
        `;
        
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        });
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }
}

// Initialize Cart Manager
document.addEventListener('DOMContentLoaded', () => {
    window.cartManager = new CartManager();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(-100%);
            opacity: 0;
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
    
    .shake {
        animation: shake 0.5s ease;
    }
`;
document.head.appendChild(style);