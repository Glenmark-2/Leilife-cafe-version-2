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
            <img id="profile_photo" src="../public/assets/cheesy_bacon_&_egg.jpeg" alt="profile photo">
        </div>
        <button type="button" class="btn-primary-custom sideTabBtns">Personal Info</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Address</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Favorites</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Order History</button>
        <button type="button" class="btn-primary-custom sideTabBtns">Settings</button>
    </div>
    <!-- personal info -->
    <section class="tab" id="personal_info" style="display: none;">
        <h3 class="tab-title">Personal Information</h3>
        <hr>
        <div style="overflow-y: auto;">

            <div class="box-input">
                <div class="info">
                    <p class="label">First Name</p>
                    <p class="display-value"><?php echo htmlspecialchars($user->first_name); ?></p>
                    <input class="edit-input" type="text" name="first_name" value="<?php echo htmlspecialchars($user->first_name); ?>" style="display:none;">
                </div>

                <div class="info">
                    <p class="label">Last Name</p>
                    <p class="display-value"><?php echo htmlspecialchars($user->last_name); ?></p>
                    <input class="edit-input" type="text" name="last_name" value="<?php echo htmlspecialchars($user->last_name); ?>" style="display:none;">
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
            <button type="button" class="btn-primary-custom">Edit</button>
        </div>
    </section>

    <!-- address -->
    <section class="tab" id="address" style="display: none;">
        <h3 class="tab-title">Address</h3>
        <hr>
        <div style="overflow-y: auto;">

            <div class="box-input">
                <div class="info">
                    <p class="label">Street</p>
                    <p class="display-value">Biringan</p>
                    <input class="edit-input" type="text" name="street" value="" style="display:none;">
                </div>

                <div class="info">
                    <p class="label">Barangay</p>
                    <p class="display-value">Cafe</p>
                    <input class="edit-input" type="text" name="barangay" value="" style="display:none;">
                </div>

                <div class="info">
                    <p class="label">City</p>
                    <p class="display-value">Caloocan City</p>
                    <input class="edit-input" type="tel" name="city" value="" style="display:none;">
                </div>

                <div class="info">
                    <p class="label">Province</p>
                    <p class="display-value">Metro Manila</p>
                    <input class="edit-input" type="text" name="province" value="" style="display:none;">
                </div>

                <div class="info">
                    <p class="label">Region</p>
                    <p class="display-value">NCR</p>
                    <input class="edit-input" type="text" name="region" value="" style="display:none;">
                </div>
            </div>
        </div>

        <div class="editDiv">
            <button type="button" class="btn-primary-custom">Edit</button>
        </div>
    </section>

    <!-- favorties -->
    <section class="tab" id="favorites" style="display: none;">
        <h3 class="tab-title">Favorites</h3>
        <hr>
        <div style="overflow-y: auto;">

            <div class="box-input">
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
                <?php include __DIR__ . "/../partials/favorite_card.php"; ?>
            </div>
        </div>
    </section>

    <!-- order histpry -->
    <section class="tab" id="order_history" style="display: none;">
        <h3 class="tab-title">Order History</h3>
        <hr>
        <div style="overflow-y: auto;">
            <div class="box-input" style="flex-direction: column; gap: 15px;">
                <?php include __DIR__ . "/../partials/order_history_card.php"; ?>
                <?php include __DIR__ . "/../partials/order_history_card.php"; ?>
                <?php include __DIR__ . "/../partials/order_history_card.php"; ?>
            </div>
        </div>
    </section>

    <!-- settings -->
    <section class="tab" id="settings" style="display: flex;">
        <h3 class="tab-title">Settings</h3>
        <hr>
        <button type="button" class="btn-primary-custom changePass" style="width: 300px;">Change password</button>
    </section>
</div>

<script src="../scripts/users/profile.js"></script>