<?php
require_once __DIR__ . '/../backend/helpers/SessionManager.php';
require_once __DIR__ . '/../backend/config/Database.php';
require_once __DIR__ . '/../backend/repositories/UserRepository.php';

SessionManager::requireLogin();
$userId = SessionManager::get('user_id');

$db = (new Database())->getConnection();
$userRepo = new UserRepository($db);
$user = $userRepo->findById($userId);

if (!$user) {
    // Handle case where user is not found (shouldn't happen if logged in properly)
    echo "User not found.";
    exit;
}
?>
<div id="body">
    <div id="sideTabs">
        <div id="imgDiv">
            <?php
            $photo = $user->profile_photo ? '../public/assets/profiles/' . $user->profile_photo : '../public/assets/default_user.png';
            ?>
            <img id="profile_photo" src="<?php echo htmlspecialchars($photo); ?>" alt="profile photo">
        </div>
        <button type="button" class="btn-primary-custom sideTabBtns">Personal Info</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Address</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Favorites</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Order History</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Settings</button>
    </div>
    <!-- personal info -->
    <section class="tab" id="personal_info" style="display: flex; ">
        <h3 class="tab-title">Personal Information</h3>
        <hr>
        <div style="overflow-y: auto;">
            <form id="personal-info-form" action="../backend/api/update_user_personal_info.php" method="POST">
                <div class="box-input">
                    <div class="info">
                        <p class="label">First Name</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->first_name))); ?></p>
                        <input class="edit-input" type="text" name="first_name" value="<?php echo ucwords(strtolower(htmlspecialchars($user->first_name))); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">Last Name</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->last_name))); ?></p>
                        <input class="edit-input" type="text" name="last_name" value="<?php echo ucwords(strtolower(htmlspecialchars($user->last_name))); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">Phone Number</p>
                        <p class="display-value"><?php echo htmlspecialchars($user->phone_number); ?></p>
                        <input class="edit-input" type="tel" name="phone_number" value="<?php echo htmlspecialchars($user->phone_number); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">Email</p>
                        <p class="display-value"><?php echo htmlspecialchars($user->email); ?></p>
                        <input class="edit-input" type="text" name="email" value="<?php echo htmlspecialchars($user->email); ?>" style="display:none;">
                    </div>
                </div>
        </div>

        <div class="editDiv">
            <button type="button" class="btn-primary-custom" id="editPersonalInfoBtn">Edit</button>
        </div>
        </form>
    </section>

    <!-- address -->
    <section class="tab" id="address" style="display: none;">
        <h3 class="tab-title">Address</h3>
        <hr>

        <div style="overflow-y: auto;">

            <?php if (!$user->street): ?>
                <button type="button" class="btn-primary-custom" id="editAddressBtn">Add Address</button>

            <?php else: ?>
                <div class="box-input">
                    <div class="info">
                        <p class="label">Street</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->street ?? 'Not set'))); ?></p>
                        <input class="edit-input" type="text" name="street" value="<?php echo ucwords(strtolower(htmlspecialchars($user->street ?? ''))); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">Barangay</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->barangay ?? 'Not set'))); ?></p>
                        <input class="edit-input" type="text" name="barangay" value="<?php echo ucwords(strtolower(htmlspecialchars($user->barangay ?? ''))); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">City</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->city ?? 'Caloocan City'))); ?></p>
                        <input class="edit-input" type="tel" name="city" value="<?php echo ucwords(strtolower(htmlspecialchars($user->city ?? ''))); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">Province</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->province ?? 'Metro Manila'))); ?></p>
                        <input class="edit-input" type="text" name="province" value="<?php echo ucwords(strtolower(htmlspecialchars($user->province ?? ''))); ?>" style="display:none;">
                    </div>

                    <div class="info">
                        <p class="label">Region</p>
                        <p class="display-value"><?php echo ucwords(strtolower(htmlspecialchars($user->region ?? 'NCR'))); ?></p>
                        <input class="edit-input" type="text" name="region" value="<?php echo ucwords(strtolower(htmlspecialchars($user->region ?? ''))); ?>" style="display:none;">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($user->street): ?>
            <div class="editDiv">
                <button type="button" class="btn-primary-custom" id="editAddressBtn">Edit</button>
            </div>
        <?php endif; ?>
    </section>

    <!-- favorties -->
    <section class="tab" id="favorites" style="display: none;">
        <h3 class="tab-title">Favorites</h3>
        <hr>
        <div style="overflow-y: auto;">

            <!-- Container for JS rendering -->
            <div id="favorites-container" class="box-input" style="flex-direction: row; flex-wrap: wrap; gap: 20px;">
                <!-- JS will populate this -->
            </div>

            <?php
            require_once __DIR__ . '/../backend/services/ProductService.php';
            $productService = new ProductService();
            $favorites = $productService->getUserFavorites($userId);
            // Prepare data for JS
            $jsFavorites = array_map(function ($fav) {
                return [
                    'id' => $fav['product_id'],
                    'name' => $fav['product_name'],
                    'price' => (float)$fav['price'],
                    'image' => $fav['image_path']
                ];
            }, $favorites);
            ?>

            <script>
                const userFavorites = <?php echo json_encode($jsFavorites); ?>;

                function capitalizeFirstLetter(string) {
                    if (!string) return '';
                    return string.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                }

                // Duplicated from menu.php for consistency as requested
                function createCardHtml(product) {
                    const baseUrl = window.BASE_URL || '/Leilife_2nd';
                    const imagePath = (product.image && typeof product.image === 'string' && product.image.trim() !== '') ?
                        ((!product.image.startsWith('http') && !product.image.startsWith('/')) ? baseUrl + '/public/assets/products/' + product.image.trim() : product.image) :
                        baseUrl + '/public/assets/products/food_photo.png';

                    const capitalizedName = capitalizeFirstLetter(product.name);

                    return `
                        <div class="col" style="width: 200px;">
                            <div class="card-box" onclick="window.location.href='index.php?page=solo_product&id=${product.id}'" style="cursor: pointer; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
                                <img class="product-image" 
                                     src="${imagePath}" 
                                     alt="${capitalizedName}" 
                                     style="width: 100%; height: 150px; object-fit: cover;"
                                     onerror="this.src='${baseUrl}/public/assets/products/food_photo.png'">
                                <div style="padding: 8px;">
                                    <p class="mb-1 text-truncate" title="${capitalizedName}" style="font-weight: bold; margin-bottom: 5px;">${capitalizedName}</p>
                                    <div id="price-div" style="display: flex; justify-content: space-between; align-items: center;">
                                        <p>₱${product.price ? parseFloat(product.price).toFixed(2) : '0.00'}</p>
                                        <button class="buyBtn btn-primary-custom" style="padding: 5px 15px; font-size: 0.8rem;">Buy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                document.addEventListener('DOMContentLoaded', () => {
                    const container = document.getElementById('favorites-container');
                    if (userFavorites && userFavorites.length > 0) {
                        container.innerHTML = userFavorites.map(product => createCardHtml(product)).join('');
                    } else {
                        container.innerHTML = '<p>No favorites yet.</p>';
                    }
                });
            </script>
        </div>
    </section>

    <!-- order histpry -->
    <section class="tab" id="order_history" style="display: none;">
        <h3 class="tab-title">Order History</h3>
        <hr>
        <div style="overflow-y: auto; max-height: 600px;">
            <div id="order-history-container" class="box-input" style="flex-direction: column; gap: 15px;">
                <!-- JS will populate this -->
                <div class="text-center p-4 w-100">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- settings -->
    <section class="tab" id="settings" style="display: none;">
        <h3 class="tab-title">Settings</h3>
        <hr>
        <?php if (!$user->password): ?>
            <button type="button" class="btn-primary-custom changePass" id="setPassBtn" style="width: 300px;">Set password</button>
        <?php else: ?>
            <button type="button" class="btn-primary-custom changePass" id="changePassBtn" style="width: 300px;">Change password</button>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . "/../components/edit_address_modal.php"; ?>

<!-- Change password modal -->
<div id="changePasswordModal" class="modal-overlay" style="display: none;">
    <div class="modal-content glass-effect" style="height: fit-content;">
        <h3 style="margin-bottom: 20px;">Change Password</h3>
        <?php if (!$user->password): ?>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="modal-input" placeholder="Enter new password" required>
                    <small id="password-strength" style="display:block; margin-top:5px; font-weight:bold;"></small>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="modal-input" placeholder="Confirm new password" required>
                </div>
                <div class="modal-actions">
                    <button type="button" id="closeChangePasswordModal" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary-custom" id="changePasswordBtn">Change Password</button>
                </div>
            </form>
        <?php else: ?>
            <form id="changePasswordForm">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="modal-input" placeholder="Enter current password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="modal-input" placeholder="Enter new password" required>
                    <small id="password-strength" style="display:block; margin-top:5px; font-weight:bold;"></small>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="modal-input" placeholder="Confirm new password" required>
                </div>
                <div class="modal-actions">
                    <button type="button" id="closeChangePasswordModal" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary-custom" id="changePasswordBtn">Change Password</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<!-- Feedback Modal -->
<div id="feedbackModal" class="modal-overlay" style="display: none; z-index: 2000;">
    <div class="modal-content glass-effect" style="max-width: 450px; height: fit-content;">
        <h3 style="margin-bottom: 20px;" id="feedbackModalTitle">Rate your Order</h3>
        <form id="feedbackForm">
            <input type="hidden" id="feedbackOrderId" name="order_id">
            <div class="form-group mb-3">
                <label class="mb-2">Rating</label>
                <div class="rating-stars d-flex gap-2 justify-content-center mb-3" style="font-size: 2rem; color: #ccc; cursor: pointer;">
                    <i class="bi bi-star-fill star" data-value="1"></i>
                    <i class="bi bi-star-fill star" data-value="2"></i>
                    <i class="bi bi-star-fill star" data-value="3"></i>
                    <i class="bi bi-star-fill star" data-value="4"></i>
                    <i class="bi bi-star-fill star" data-value="5"></i>
                </div>
                <input type="hidden" name="rating" id="ratingInput" required>
            </div>
            <div class="form-group mb-4">
                <label class="mb-2">Your Feedback (Optional)</label>
                <textarea name="comment" class="form-control" rows="3" placeholder="Tell us about your experience..." style="border-radius: 10px;"></textarea>
            </div>
            <div class="modal-actions d-flex gap-3">
                <button type="button" onclick="closeFeedbackModal()" class="btn btn-secondary flex-fill" style="color: black;">Cancel</button>
                <button type="submit" class="btn btn-primary-custom flex-fill">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<script src="../scripts/users/profile.js"></script>