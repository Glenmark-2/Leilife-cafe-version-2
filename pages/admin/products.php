<div class="title-content">
    <div class="title-div">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <p class="title">Products</p>
    </div>
    <div>
        <button type="button" class="btn-primary-custom btns">View Archive</button>
    </div>
</div>
<!-- sub category buttons -->
<div>
    <button type="button" class="btn btn-outline-primary">Milktea</button>
    <button type="button" class="btn btn-outline-primary">Tea</button>
    <button type="button" class="btn btn-outline-primary">Coffee</button>
</div>

<hr>

<div id="search_add">
    <form class="search-bar" role="search" style="margin-bottom: 0;">
        <input type="search" id="search-input" placeholder="Search staff name" aria-label="Search staff">
    </form>
    <div class="add-container">
        <button type="button" class="btn-primary-custom btns">Add new product</button>
    </div>
</div>


<div class="table-container">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th class="nameCol">Name</th>
                    <th class="priceCol">Price</th>
                    <th class="catCol">Category</th>
                    <th class="statusCol">Status</th>
                    <th class="actionsCol">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="nameCol">
                        <div class="prodNameDiv">
                            <img src="__DIR__./../../public/assets/cheesy_bacon_&_egg.jpeg" alt="" class="productPhoto">
                            <p>Cheesy Bacon with Egg</p>
                        </div>
                    </td>
                    <td class="priceCol">P 100.00</td>
                    <td class="catCol">Milktea</td>
                    <td class="statusCol">Available</td>
                    <td class="actionsCol">
                        <div class="action-header-buttons">
                            <button title="edit" type="button" class="edit-btn"><img src="../public/assets/pencil.png" class="edit-icon"></button>
                            <button title="archive" type="button" class="archive-btn"><img src="../public/assets/archive.png" class="archive-icon"></button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>