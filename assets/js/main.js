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

// === WISHLIST / CART MANAGER ===
class CartManager {
    constructor() {
        this.cartDrawer = document.getElementById('cart-drawer');
        this.cartOverlay = document.getElementById('cart-overlay');
        this.cartBtn = document.getElementById('cart-btn');
        this.closeCartBtn = document.getElementById('close-cart');
        this.cartItemsContainer = document.getElementById('cart-items');
        this.cartCount = document.getElementById('cart-count');
        
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
                // Total tidak lagi digunakan di Wishlist mode
                this.renderCart(data.items);
            }
        } catch (error) {
            console.error('Error loading cart:', error);
        }
    }
    
    renderCart(items) {
        if (!this.cartItemsContainer) return;
        
        if (items.length === 0) {
            this.cartItemsContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-heart-break text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted">Belum ada barang disukai</p>
                </div>
            `;
            return;
        }
        
        // --- LOGIC BARU: Hapus tombol + dan - ---
        this.cartItemsContainer.innerHTML = items.map(item => `
            <div class="cart-item p-3 border-bottom" data-cart-id="${item.id}">
                <div class="d-flex gap-3 align-items-center">
                    <img src="${item.image}" alt="${item.name}" 
                         style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px;">
                    
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold" style="font-size: 0.95rem;">${item.name}</h6>
                        <p class="text-primary fw-bold mb-0" style="font-size: 0.9rem;">
                            Rp ${parseInt(item.price).toLocaleString('id-ID')}
                        </p>
                    </div>

                    <button class="btn btn-sm text-danger" onclick="cart.removeItem(${item.id})" title="Hapus">
                        <i class="bi bi-trash fs-5"></i>
                    </button>
                </div>
            </div>
        `).join('');
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
                this.showToast(data.message || 'Ditambahkan ke Favorit', 'success'); // Gunakan pesan dari backend
                this.updateCartCount();
                // Opsional: Buka drawer otomatis saat dilike
                // this.openCart(); 
            } else {
                // Jika gagal (misal sudah ada), tetap tampilkan toast tapi mungkin warning/info
                this.showToast(data.message || 'Gagal menambahkan', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showToast('Terjadi kesalahan', 'error');
        }
    }
    
    // Fungsi Update Quantity DIHAPUS karena tidak dipakai lagi di Wishlist
    
    async removeItem(cartId) {
        if (!confirm('Hapus dari daftar disukai?')) return;
        
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
                this.showToast('Dihapus dari favorit', 'success');
            }
        } catch (error) {
            console.error('Error removing item:', error);
        }
    }
    
    async updateCartCount() {
        try {
            const response = await fetch(`${BASE_URL}/ajax/get-cart-count.php`);
            const data = await response.json();
            
            if (this.cartCount) {
                if (data.count > 0) {
                    this.cartCount.textContent = data.count;
                    this.cartCount.style.display = 'inline-block';
                } else {
                    this.cartCount.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
        }
    }
    
    showToast(message, type = 'success') {
        // Hapus toast lama jika ada agar tidak menumpuk
        const oldToast = document.querySelector('.toast-modern');
        if(oldToast) oldToast.remove();

        const toast = document.createElement('div');
        toast.className = 'toast-modern';
        // Warna icon disesuaikan
        const iconClass = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-info-circle-fill text-warning';
        
        toast.innerHTML = `
            <i class="bi ${iconClass} fs-5"></i>
            <span class="fw-medium">${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Animasi Masuk
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });
        
        setTimeout(() => {
            toast.style.transition = 'all 0.3s ease';
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
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

console.log('🎨 Thrift & Swap - Wishlist Mode Active');