<!-- Menu Page -->
<?php
// Mock Data Structure
$menuData = [
    'Meals' => [
        'Rice Meals' => [
            ['name' => 'Pork Chao Fan', 'price' => 99.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Beef Chao Fan', 'price' => 109.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Chicken Teriyaki', 'price' => 129.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Pork Sisig', 'price' => 119.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Chicken Adobo', 'price' => 115.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Beef Steak', 'price' => 135.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
        ],
        'Pasta' => [
            ['name' => 'Creamy Carbonara', 'price' => 149.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Spaghetti Bolognese', 'price' => 139.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Tuna Pesto', 'price' => 145.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
        ]
    ],
    'Drinks' => [
        'Coffee' => [
            ['name' => 'Iced Americano', 'price' => 85.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Caramel Macchiato', 'price' => 110.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Vanilla Latte', 'price' => 105.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Cappuccino', 'price' => 95.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Espresso', 'price' => 75.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
        ],
        'Refreshers' => [
            ['name' => 'Lemonade', 'price' => 70.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Cucumber Lemon', 'price' => 75.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Blue Lemonade', 'price' => 75.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
        ]
    ],
    'Featured' => [
        'Best Sellers' => [
            ['name' => 'Leilife Special Burger', 'price' => 189.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Clubhouse Sandwich', 'price' => 160.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
            ['name' => 'Signature Milk Tea', 'price' => 100.00, 'image' => '../public/assets/cheesy_bacon_&_egg.jpeg'],
        ]
    ]
];
?>

<div id="menu-body" class="container my-4">
    <!-- Desktop Navigation (Web devices > 576px) -->
    <div class="d-none d-sm-flex justify-content-start gap-3 mb-5 border-bottom pb-3">
        <?php foreach (array_keys($menuData) as $category): ?>
            <button type="button" 
                    class="btn btn-primary-custom main-cat-desktop <?php echo $category === 'Meals' ? 'active' : ''; ?>" 
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
                    class="btn btn-sm main-cat-mobile me-2 <?php echo $category === 'Meals' ? 'active' : ''; ?>" 
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

    /**
     * Renders a single product card HTML using the structure from partials/menu_card.php
     */
    function createCardHtml(product) {
        // Using "card-box" structure strictly as requested.
        // CSS handles width and margins now.
        return `
            <div class="col">
                <div class="card-box">
                    <img class="product-image" src="${product.image}" alt="${product.name}">
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

        const subCategories = menuData[categoryName];
        if (!subCategories) return;

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
        switchCategory('Meals');

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

