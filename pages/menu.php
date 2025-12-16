<!-- Menu Page -->
<?php
require_once __DIR__ . '/../backend/services/ProductService.php';

$productService = new ProductService();
$menuData = $productService->getMenuStructure();

// Fallback if empty (optional, but good for stability if DB is empty)
if (empty($menuData)) {
    $menuData = []; 
}

// Determine the first category to show by default
$firstCategory = array_key_first($menuData);
?>

<div id="menu-body" class="container my-4">
    <!-- Desktop Navigation (Web devices > 576px) -->
    <div class="d-none d-sm-flex justify-content-start gap-3 mb-5 border-bottom pb-3">
        <?php foreach (array_keys($menuData) as $category): ?>
            <button type="button" 
                    class="btn btn-primary-custom main-cat-desktop <?php echo $category === $firstCategory ? 'active' : ''; ?>" 
                    onclick="switchCategory('<?php echo $category; ?>')">
                <?php echo $category; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Mobile Navigation (Small screens < 576px) -->
    <!-- Sentinel to detect when we scroll past this point -->
    <div id="sticky-sentinel" class="d-block d-sm-none"></div>
    
    <div id="mobileNav" class="d-flex d-sm-none overflow-auto pb-3 mb-4 mobile-cat-scroll" style="white-space: nowrap;">
        <?php foreach (array_keys($menuData) as $category): ?>
            <button type="button" 
                    class="btn btn-sm main-cat-mobile me-2 <?php echo $category === $firstCategory ? 'active' : ''; ?>" 
                    onclick="switchCategory('<?php echo $category; ?>')">
                <?php echo $category; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Content Area -->
    <div id="menu-content">
        <!-- JS will populate this -->
    </div>
</div>

<script>
    const menuData = <?php echo json_encode($menuData); ?>;
    const initialCategory = <?php echo json_encode($firstCategory); ?>;

    /**
     * Renders a single product card HTML using the structure from partials/menu_card.php
     */
    function createCardHtml(product) {
        // Using "card-box" structure strictly as requested.
        // CSS handles width and margins now.
        return `
            <div class="col">
                <div class="card-box" onclick="window.location.href='index.php?page=solo_product&id=${product.id}'" style="cursor: pointer;">
                    <img class="product-image" src="${(product.image && typeof product.image === 'string' && product.image.trim() !== '' ? ((!product.image.startsWith('http') && !product.image.startsWith('/')) ? '/Leilife_2nd/public/assets/products/' + product.image.trim() : product.image) : '/Leilife_2nd/public/assets/products/not_available.png')}" alt="${product.name}">
                    <div style="padding: 8px;">
                        <p class="mb-1 text-truncate" title="${product.name}">${product.name}</p>
                        <div id="price-div">
                            <p>₱${product.price.toFixed(2)}</p>
                            <button class="buyBtn">Buy</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Renders the selected category's subcategories and products
     */
    function renderCategory(categoryName) {
        const container = document.getElementById('menu-content');
        container.innerHTML = ''; // Clear current content

        if (!menuData || !menuData[categoryName]) return;

        const subCategories = menuData[categoryName];

        // Loop deeply to maintain order
        Object.entries(subCategories).forEach(([subCatName, products]) => {
            // Subcategory Title
            const section = document.createElement('div');
            section.className = 'mb-5';
            
            const title = document.createElement('h4');
            title.className = 'mb-4 fw-bold text-secondary text-uppercase';
            title.style.letterSpacing = '1px';
            title.textContent = subCatName;
            section.appendChild(title);

            // Grid Container for 5 Columns
            // responsive: 
            // - 2 cols on mobile < 576px
            // - 3 cols on sm (small tablets) >= 576px
            // - 4 cols on md (tablets/laptops) >= 768px
            // - 5 cols on lg (desktops) >= 992px
            const grid = document.createElement('div');
            // Align start (default) so items start from left
            grid.className = 'row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-3 g-md-4'; 
            
            // Generate Cards
            grid.innerHTML = products.map(product => createCardHtml(product)).join('');
            
            section.appendChild(grid);
            container.appendChild(section);
        });
    }

    // Tab Switching Logic
    function switchCategory(categoryName) {
        // Update Desktop Buttons
        document.querySelectorAll('.main-cat-desktop').forEach(btn => {
            if (btn.textContent.trim() === categoryName) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Update Mobile Buttons
        document.querySelectorAll('.main-cat-mobile').forEach(btn => {
            if (btn.textContent.trim() === categoryName) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Render Content
        renderCategory(categoryName);
    }

    // Initialize Default
    document.addEventListener('DOMContentLoaded', () => {
        if (initialCategory) {
            switchCategory(initialCategory);
        }

        // Sticky Mobile Header Logic
        const mobileNav = document.getElementById('mobileNav');
        const sentinel = document.getElementById('sticky-sentinel');
        const navbar = document.querySelector('.navbar');
        
        if (mobileNav && sentinel && navbar) {
            const headerHeight = navbar.offsetHeight;
            // Set css var for sticky top position
            mobileNav.style.top = (headerHeight) + 'px'; // 10px gap

            // Use IntersectionObserver to detect when sentinel scrolls past the header
            const observer = new IntersectionObserver(([e]) => {
                // If sentinel is NOT visible and we are scrolling down (boundingClientRect.top is negative or smaller than header)
                // Actually simple check: Is sentinel above the stick point?
                if (e.boundingClientRect.top < headerHeight) {
                     mobileNav.classList.add('is-stuck');
                } else {
                     mobileNav.classList.remove('is-stuck');
                }
            }, {
                root: null,
                threshold: [0, 1],
                rootMargin: `-${headerHeight}px 0px 0px 0px`
            });

            observer.observe(sentinel);
        }
    });
</script>
