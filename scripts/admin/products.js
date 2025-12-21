document.addEventListener('DOMContentLoaded', () => {
    const productsTableBody = document.getElementById('products-body');
    const searchInput = document.getElementById('product-search');
    const categoryButtons = document.querySelectorAll('.category-btn');
    const paginationControls = document.getElementById('pagination-controls');
    const paginationInfo = document.getElementById('pagination-info');
    const toggleArchiveBtn = document.getElementById('toggle-archive');

    let localProducts = [...allProducts]; // Use local copy to manage updates
    console.log("Initial products loaded:", localProducts.length);
    let filteredProducts = [];
    let currentPage = 1;
    const itemsPerPage = 10;
    let currentCategory = 'all';
    let showingArchived = false;

    function renderTable() {
        productsTableBody.innerHTML = '';

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredProducts.length);
        const paginatedProducts = filteredProducts.slice(startIndex, endIndex);

        if (paginatedProducts.length === 0) {
            productsTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px;">No ${showingArchived ? 'archived' : ''} products found.</td></tr>`;
            updatePagination(0);
            return;
        }

        paginatedProducts.forEach(product => {
            const row = document.createElement('tr');

            const imageFilename = (product.image_path && product.image_path.trim() !== '') ? product.image_path.trim().replace(/\s+/g, '_') : 'not_available.png';
            const imagePath = `assets/products/${imageFilename}`;

            const statusClass = product.is_available == 1 ? 'status-available' : 'status-unavailable';
            const statusText = product.is_available == 1 ? 'Available' : 'Unavailable';

            const archiveTitle = product.is_archived == 1 ? 'Restore' : 'Archive';
            const archiveIcon = product.is_archived == 1 ? 'restore.png' : 'archive.png';

            row.innerHTML = `
                <td class="nameCol">
                    <div class="prodNameDiv">
                        <img src="${imagePath}" alt="${product.name}" class="productPhoto" onerror="this.src='assets/leilife.png'">
                        <div>
                            <p class="prodName">${product.name} ${product.is_archived == 1 ? '<span class="badge bg-secondary">Archived</span>' : ''}</p>
                            <p class="prodDescription">${product.description || 'No description available.'}</p>
                        </div>
                    </div>
                </td>
                <td class="priceCol">P ${parseFloat(product.price).toFixed(2)}</td>
                <td class="catCol">${product.category_name || 'N/A'}</td>
                <td class="statusCol"><span class="${statusClass}">${statusText}</span></td>
                <td class="actionsCol">
                    <div class="action-header-buttons">
                        <button title="edit" type="button" class="edit-btn" onclick="editProduct(${product.product_id})">
                            <i class="bi bi-pencil-square" style="font-size: 1.2rem; color: #d0b28c;"></i>
                        </button>
                        <button title="${archiveTitle}" type="button" class="archive-btn" onclick="toggleArchive(${product.product_id}, ${product.is_archived})">
                            <i class="bi ${product.is_archived == 1 ? 'bi-arrow-counterclockwise' : 'bi-archive'}" style="font-size: 1.2rem; color: #6c757d;"></i>
                        </button>
                    </div>
                </td>
            `;
            productsTableBody.appendChild(row);
        });

        updatePagination(filteredProducts.length);
        updatePaginationInfo(startIndex + 1, endIndex, filteredProducts.length);
    }

    function updatePagination(totalItems) {
        paginationControls.innerHTML = '';
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        prevLi.onclick = (e) => {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        };
        paginationControls.appendChild(prevLi);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.onclick = (e) => {
                e.preventDefault();
                currentPage = i;
                renderTable();
            };
            paginationControls.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        nextLi.onclick = (e) => {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        };
        paginationControls.appendChild(nextLi);
    }

    function updatePaginationInfo(start, end, total) {
        if (total === 0) {
            paginationInfo.innerText = 'Showing 0 to 0 of 0 entries';
        } else {
            paginationInfo.innerText = `Showing ${start} to ${end} of ${total} entries`;
        }
    }

    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();

        filteredProducts = localProducts.filter(product => {
            const matchesSearch = product.name.toLowerCase().includes(searchTerm) ||
                (product.description && product.description.toLowerCase().includes(searchTerm));

            const matchesCategory = currentCategory === 'all' ||
                product.category_id == currentCategory ||
                product.parent_category_id == currentCategory;

            const matchesArchive = (product.is_archived || 0) == (showingArchived ? 1 : 0);

            return matchesSearch && matchesCategory && matchesArchive;
        });

        currentPage = 1;
        renderTable();
    }

    searchInput.addEventListener('input', filterProducts);

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCategory = btn.getAttribute('data-category-id');
            filterProducts();
        });
    });

    toggleArchiveBtn.addEventListener('click', () => {
        showingArchived = !showingArchived;
        toggleArchiveBtn.innerText = showingArchived ? 'View Active' : 'View Archive';
        filterProducts();
    });

    // Make toggleArchive accessible globally
    window.toggleArchive = async function (id, currentArchived) {
        const confirmMsg = currentArchived == 1 ? "Are you sure you want to restore this product?" : "Are you sure you want to archive this product?";
        if (!confirm(confirmMsg)) return;

        try {
            const response = await fetch('../backend/api/admin/archive_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: id,
                    is_archived: currentArchived == 1 ? 0 : 1
                })
            });
            const result = await response.json();
            if (result.success) {
                // Update local data
                const prodIndex = localProducts.findIndex(p => p.product_id == id);
                if (prodIndex !== -1) {
                    localProducts[prodIndex].is_archived = currentArchived == 1 ? 0 : 1;
                }
                filterProducts();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error toggling archive:', error);
            alert('An error occurred. Please try again.');
        }
    };

    // Initial filter and render
    filterProducts();
});

function editProduct(id) {
    console.log('Edit product:', id);
    // Future implementation: open edit modal
}
