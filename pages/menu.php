<!-- sample lang -->
<?php
$categories = [
    'Meals' => ['Burger', 'Pizza', 'Pasta'],
    'Drinks' => ['Milk Tea', 'Coffee', 'Tea'],
    'Desserts' => ['Cake', 'Ice Cream', 'Brownie']
];
?>

<div id="menu-body">
    <div id="mainCat">
        <?php foreach ($categories as $mainCat => $subCat): ?>
            <button type="button" class="btn-primary-custom main-categories"><?= $mainCat ?></button>
        <?php endforeach; ?>
    </div>

    <div id="subCat">
    </div>
    <?php include __DIR__ . "/../partials/menu_card.php"; ?>

</div>

<script>
    document.querySelectorAll(".main-categories").forEach(btn => {
        btn.addEventListener("click", function() {
            document.querySelectorAll(".main-categories").forEach(b => b.classList.remove("active"));
            this.classList.add("active");

        });
    });

    const categories = <?php echo json_encode($categories); ?>;
    const subCat = document.getElementById('subCat');

    function renderSubCategories(mainCat) {
        subCat.innerHTML = "";
        categories[mainCat].forEach(sub => {
            const btn = document.createElement("button");
            btn.className = "btn-primary-custom sub-categories";
            btn.textContent = sub;
            btn.addEventListener("click", () => {
                subCat.querySelectorAll(".sub-categories").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
            });
            subCat.appendChild(btn);
        });
    }

    document.querySelectorAll(".main-categories").forEach(btn => {
        btn.addEventListener("click", function() {
            document.querySelectorAll(".main-categories").forEach(b => b.classList.remove("active"));
            this.classList.add("active");
            renderSubCategories(this.textContent);
        });
    });

    // Default main category
    const defaultMain = "Meals";
    document.querySelectorAll(".main-categories").forEach(btn => {
        if (btn.textContent === defaultMain) btn.click();
    });
</script>