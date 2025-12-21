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

    const addProductBtn = document.getElementById('add-product-btn');
    const editForm = document.getElementById('editProductForm');
    const editModalElement = document.getElementById('editProductModal');
    const editModal = new bootstrap.Modal(editModalElement);
    const modalTitle = document.getElementById('editProductModalLabel');
    const saveProductBtn = document.getElementById('save-product-btn');

    addProductBtn.addEventListener('click', () => {
        // Reset form
        editForm.reset();
        editForm.classList.remove('was-validated');
        document.getElementById('edit-product-id').value = '';
        document.getElementById('edit-img-preview').src = 'assets/products/not_available.png';

        // Update Modal UI
        modalTitle.innerText = 'Add New Product';
        saveProductBtn.innerText = 'Add Product';

        editModal.show();
    });

    // Make editProduct accessible
    window.editProduct = function (id) {
        const product = localProducts.find(p => p.product_id == id);
        if (!product) return;

        // Reset validation
        editForm.classList.remove('was-validated');

        // Populate Modal
        document.getElementById('edit-product-id').value = product.product_id;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-description').value = product.description || '';
        document.getElementById('edit-price').value = product.price;
        document.getElementById('edit-category').value = product.category_id;
        document.getElementById('edit-status').value = product.is_available;

        // Populate Image Preview
        const preview = document.getElementById('edit-img-preview');
        const filename = (product.image_path && product.image_path.trim() !== '') ? product.image_path.trim().replace(/\s+/g, '_') : 'not_available.png';
        preview.src = `assets/products/${filename}`;

        // Reset file input
        document.getElementById('edit-image').value = '';

        // Update Modal UI
        modalTitle.innerText = 'Edit Product Information';
        saveProductBtn.innerText = 'Save Changes';

        editModal.show();
    };

    // Link preview for the file input
    document.getElementById('edit-image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                document.getElementById('edit-img-preview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    saveProductBtn.addEventListener('click', async () => {
        // Validation
        if (!editForm.checkValidity()) {
            editForm.classList.add('was-validated');
            return;
        }

        const productId = document.getElementById('edit-product-id').value;
        const isEdit = productId !== '';

        // Use FormData for file upload
        const formData = new FormData();
        if (isEdit) formData.append('product_id', productId);
        formData.append('name', document.getElementById('edit-name').value.trim());
        formData.append('description', document.getElementById('edit-description').value.trim());
        formData.append('price', document.getElementById('edit-price').value);
        formData.append('category_id', document.getElementById('edit-category').value);
        formData.append('is_available', document.getElementById('edit-status').value);

        const imageFile = document.getElementById('edit-image').files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        try {
            saveProductBtn.disabled = true;
            saveProductBtn.innerText = 'Saving...';

            const endpoint = isEdit ? '../backend/api/admin/update_product.php' : '../backend/api/admin/add_product.php';
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData // Body is now FormData
            });

            const responseText = await response.text();
            console.log("Response from server:", responseText);

            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error("Failed to parse JSON:", responseText);
                throw new Error("Invalid server response");
            }

            if (result.success) {
                if (isEdit) {
                    // Update local data for real-time refresh
                    const prodIndex = localProducts.findIndex(p => p.product_id == productId);
                    if (prodIndex !== -1) {
                        // Update object properties
                        localProducts[prodIndex].name = formData.get('name');
                        localProducts[prodIndex].description = formData.get('description');
                        localProducts[prodIndex].price = parseFloat(formData.get('price'));
                        localProducts[prodIndex].category_id = formData.get('category_id');
                        localProducts[prodIndex].is_available = formData.get('is_available');

                        if (result.new_image_path) {
                            localProducts[prodIndex].image_path = result.new_image_path;
                        }

                        // Also update category name visually in table
                        const catSelect = document.getElementById('edit-category');
                        localProducts[prodIndex].category_name = catSelect.options[catSelect.selectedIndex].text.replace('— ', '');
                    }
                } else {
                    // Handle New Product Refresh (Ideally fetch again or add manually)
                    // For now, let's just refresh the whole list to be safe or add it if we have all data
                    alert('Product added successfully!');
                    window.location.reload(); // Refresh to get all required data like category_name
                    return;
                }

                editModal.hide();
                filterProducts();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error updating product:', error);
            alert('An error occurred while saving.');
        } finally {
            saveProductBtn.disabled = false;
            saveProductBtn.innerText = 'Save Changes';
        }
    });

    // Initial filter and render
    filterProducts();
});
