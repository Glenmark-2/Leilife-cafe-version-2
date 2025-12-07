 const pickupRadio = document.getElementById('pickup');
    const homeRadio = document.getElementById('homeDelivery');
    const pickupAddress = document.getElementById('pickupAddress');
    const homeInputs = document.getElementById('homeDeliveryInputs');
    const editBtnDel = document.getElementById('editBtn-del');

function toggleDeliveryOptions() {
    if (pickupRadio.checked) {
        pickupAddress.classList.remove('d-none');
        homeInputs.classList.add('d-none');
        editBtnDel.classList.add('d-none');
    } else if (homeRadio.checked) {
        pickupAddress.classList.add('d-none');
        homeInputs.classList.remove('d-none');
        editBtnDel.classList.remove('d-none');
    }
}



    // Initialize display on page load
    toggleDeliveryOptions();

    // Listen for changes
    pickupRadio.addEventListener('change', toggleDeliveryOptions);
    homeRadio.addEventListener('change', toggleDeliveryOptions);