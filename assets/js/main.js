// === THEME TOGGLE (Dark Mode) ===
const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

// Load saved theme
const currentTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', currentTheme);

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const theme = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    });
}

// === CART FUNCTIONALITY ===
class CartManager {
    constructor() {
        this.cartDrawer = document.getElementById('cart-drawer');
        this.cartOverlay = document.getElementById('cart-overlay');
        this.cartBtn = document.getElementById('cart-btn');
        this.closeCartBtn = document.getElementById('close-cart');
        this.cartItemsContainer = document.getElementById('cart-items');
        this.cartCount = document.getElementById('cart-count');
        this.cartTotal = document.getElementById('cart-total');
        
        this.init();
    }
    
    init() {
        if (this.cartBtn) {
            this.cartBtn.addEventListener('click', () => this.openCart());
        }
        
        if (this.closeCartBtn) {
            this.closeCartBtn.addEventListener('click', () => this.closeCart());
        }
        
        if (this.cartOverlay) {
            this.cartOverlay.addEventListener('click', () => this.closeCart());
        }
        
        // Load cart on page load
        this.loadCart();
        this.updateCartCount();
    }
    
    openCart() {
        if (this.cartDrawer && this.cartOverlay) {
            this.cartDrawer.classList.add('active');
            this.cartOverlay.classList.add('active');
            this.loadCart();
        }
    }
    
    closeCart() {
        if (this.cartDrawer && this.cartOverlay) {
            this.cartDrawer.classList.remove('active');
            this.cartOverlay.classList.remove('active');
        }
    }
    
    async loadCart() {
        try {
            const response = await fetch(`${BASE_URL}/ajax/get-cart.php`);
            const data = await response.json();
            
            if (data.success) {
                this.renderCart(data.items, data.total);
            }
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }
    
    renderCart(items, total) {
        if (!this.cartItemsContainer) return;
        
        if (items.length === 0) {
            this.cartItemsContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="mt-3 text-muted">Keranjang kosong</p>
                </div>
            `;
            if (this.cartTotal) this.cartTotal.textContent = 'Rp 0';
            return;
        }
        
        this.cartItemsContainer.innerHTML = items.map(item => `
            <div class="cart-item p-3 border-bottom" data-cart-id="${item.id}">
                <div class="d-flex gap-3">
                    <img src="${item.image}" alt="${item.name}" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${item.name}</h6>
                        <p class="text-muted small mb-2">Rp ${parseInt(item.price).toLocaleString('id-ID')}</p>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span class="px-3">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})">
                                <i class="bi bi-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger ms-auto" onclick="cart.removeItem(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        if (this.cartTotal) {
            this.cartTotal.textContent = `Rp ${parseInt(total).toLocaleString('id-ID')}`;
        }
    }
    
    async addToCart(productId) {
        try {
            const response = await fetch(`${BASE_URL}/ajax/add-to-cart.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Produk ditambahkan ke keranjang', 'success');
                this.updateCartCount();
                this.openCart();
            } else {
                this.showToast(data.message || 'Gagal menambahkan ke keranjang', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showToast('Terjadi kesalahan', 'error');
        }
    }
    
    async updateQuantity(cartId, quantity) {
        if (quantity < 1) {
            this.removeItem(cartId);
            return;
        }
        
        try {
            const response = await fetch(`${BASE_URL}/ajax/update-cart.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_id: cartId, quantity })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.loadCart();
                this.updateCartCount();
            }
        } catch (error) {
            console.error('Error updating cart:', error);
        }
    }
    
    async removeItem(cartId) {
        if (!confirm('Hapus item dari keranjang?')) return;
        
        try {
            const response = await fetch(`${BASE_URL}/ajax/remove-from-cart.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart_id: cartId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.loadCart();
                this.updateCartCount();
                this.showToast('Item dihapus', 'success');
            }
        } catch (error) {
            console.error('Error removing item:', error);
        }
    }
    
    async updateCartCount() {
        try {
            const response = await fetch(`${BASE_URL}/ajax/get-cart-count.php`);
            const data = await response.json();
            
            if (this.cartCount && data.count > 0) {
                this.cartCount.textContent = data.count;
                this.cartCount.style.display = 'inline-block';
            } else if (this.cartCount) {
                this.cartCount.style.display = 'none';
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
        }
    }
    
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast-modern';
        toast.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize cart manager
const cart = new CartManager();

// === LIVE SEARCH ===
const searchInput = document.getElementById('search-input');
if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const query = e.target.value.trim();
            if (query.length > 2) {
                window.location.href = `${BASE_URL}/pages/products.php?search=${encodeURIComponent(query)}`;
            }
        }, 500);
    });
}

// === SMOOTH SCROLL ===
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// === IMAGE LAZY LOADING ===
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('skeleton');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// === FORM VALIDATION ===
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});

// === PRODUCT FILTER (for products page) ===
const filterButtons = document.querySelectorAll('.filter-btn');
filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const category = btn.dataset.category;
        window.location.href = `${BASE_URL}/pages/products.php?category=${encodeURIComponent(category)}`;
    });
});

console.log('🎨 Thrift & Swap - Modern UI Loaded');
console.log('✨ Dark Mode: ' + (html.getAttribute('data-theme') === 'dark' ? 'ON' : 'OFF'));