<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../../app/models/models.php';
$model = new models();

$role = $_SESSION['role'] ?? '';


$devices_res = $model->getDevicesForSearching($role);



function getPillClass(string $condition): string {
    return match ($condition) {
        'New', 'Good' => 'c-pill c-pill--success',
        'Needs Repair' => 'c-pill c-pill--warning',
        'Damaged' => 'c-pill c-pill--danger',
        default => 'c-pill',
    };
}

function getStatusPillClass(string $status): string {
    return match ($status) {
        'Available' => 'c-pill c-pill--success',
        'In Use' => 'c-pill c-pill--warning',
        'Under Maintenance' => 'c-pill c-pill--danger',
        default => 'c-pill',
    };
}
?>


<div class="card border-0 shadow-none">
    <div class="card-body">
        <div class="d-flex align-items-start justify-content-start mb-3">
            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#createDeviceModal">
                <i class="bi bi-plus"></i> Device
            </button>
        </div>
        <hr>
        <div class="mb-3 d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text" id="search-addon">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="deviceSearch" class="form-control" 
                    placeholder="Search by Name, Model, Brand, Condition, Status, PR, Category" 
                    aria-label="Search" aria-describedby="search-addon">
            </div>
        </div>

        <div class="d-flex align-items-end justify-content-end mt-2 mb-2">
            <button class="btn btn-secondary mx-2" type="button" data-toggle="modal" data-target="#filterSearchToPrintModal">
                <i class="bi bi-printer"></i> Print Records
            </button>
            <button class="btn btn-success" type="button" onclick="refresh_web_browser()">
                <i class="bi bi-arrow-clockwise"></i> Refresh Table
            </button>
        </div>


                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Name</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Model</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Brand</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Serial Number</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Category</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Condition</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Current Status</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">PR</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Qnty</th>
                                <th scope="col" class="text-nowrap" style="text-wrap: nowrap;">Borrower (Optional)</th>
                                <th scope="col" class="text-nowwrap" style="text-wrap: nowrap;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($devices_res)): ?>
                                <?php foreach ($devices_res as $device): ?>
                                    <tr>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['name']) ?></td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['model']) ?></td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['brand']) ?></td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['serial_number']) ?></td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['category']) ?></td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;">
                                            <span class="<?= getPillClass($device['device_condition']) ?>">
                                                <?= htmlspecialchars($device['device_condition']) ?>
                                            </span>
                                        </td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;">
                                            <span class="<?= getStatusPillClass($device['current_status']) ?>">
                                                <?= htmlspecialchars($device['current_status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['pr']) ?></td>
                                        <td class="text-nowwrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['quantity']) ?></td>
                                        <td class="text-nowrap" style="text-wrap: nowrap;"><?= htmlspecialchars($device['borrower']) ?></td>
                                        <td class="d-flex gap-2 justify-content-center" style="text-wrap: nowrap;">
                                            <button class="btn btn-primary btn-sm py-1 px-2 mx-2 editDeviceBtn" 
                                                    data-toggle="modal" data-target="#editDeviceModal" data-id="<?= htmlspecialchars($device['d_uid']) ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm py-1 px-2 archiveDeviceBtn" 
                                                    data-id="<?= htmlspecialchars($device['d_uid']) ?>">
                                                <i class="bi bi-archive"></i> Move Archive
                                            </button>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No devices available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                



    </div>
</div>

<div class="modal fade" id="createDeviceModal" tabindex="-1" aria-labelledby="createDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDeviceModalLabel">Add Device</h5>
            </div>

            <div class="modal-body">
                <form id="createDeviceForm">
                    <div class="container-fluid">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="deviceName">Device Name</label>
                                <input type="text" class="form-control" id="deviceName" name="deviceName" required>
                            </div>
                            <div class="col-md-4">
                                <label for="deviceModel">Model</label>
                                <input type="text" class="form-control" id="deviceModel" name="deviceModel" required>
                            </div>
                            <div class="col-md-4">
                                <label for="deviceBrand">Brand</label>
                                <input type="text" class="form-control" id="deviceBrand" name="deviceBrand" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="deviceSerial">Serial Number</label>
                                <input type="text" class="form-control" id="deviceSerial" name="deviceSerial" required>
                            </div>
                            <div class="col-md-4">
                                <label for="deviceCategory">Category</label>
                                <input type="text" class="form-control" id="deviceCategory" name="deviceCategory">
                            </div>
                            <div class="col-md-4">
                                <label for="deviceCondition">Condition</label>
                                <select class="form-control" id="deviceCondition" name="deviceCondition">
                                    <option>New</option>
                                    <option>Good</option>
                                    <option>Needs Repair</option>
                                    <option>Damaged</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="deviceStatus">Current Status</label>
                                <select class="form-control" id="deviceStatus" name="deviceStatus">
                                    <option>In Use</option>
                                    <option>Available</option>
                                    <option>Under Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity">
                            </div>
                            <div class="col-md-4">
                                <label for="devicePR">PR</label>
                                <input type="text" class="form-control" id="devicePR" name="devicePR">
                            </div>

                            <div class="col-md-12">
                                <label for="borrower">Borrower (Optional)</label>
                                <textarea name="borrower" id="borrower" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" form="createDeviceForm" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editDeviceModal" tabindex="-1" aria-labelledby="editDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDeviceModalLabel">Edit Device</h5>
            </div>

            <div class="modal-body">
                <form id="editDeviceForm">
                    <input type="hidden" id="editDeviceId" name="d_uid">
                    <div class="container-fluid">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="editDeviceName">Device Name</label>
                                <input type="text" class="form-control" id="editDeviceName" name="name" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editDeviceModel">Model</label>
                                <input type="text" class="form-control" id="editDeviceModel" name="model" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editDeviceBrand">Brand</label>
                                <input type="text" class="form-control" id="editDeviceBrand" name="brand" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="editDeviceSerial">Serial Number</label>
                                <input type="text" class="form-control" id="editDeviceSerial" name="serial_number" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editDeviceCategory">Category</label>
                                <input type="text" class="form-control" id="editDeviceCategory" name="category">
                            </div>
                            <div class="col-md-4">
                                <label for="editDeviceCondition">Condition</label>
                                <select class="form-control" id="editDeviceCondition" name="device_condition">
                                    <option>New</option>
                                    <option>Good</option>
                                    <option>Needs Repair</option>
                                    <option>Damaged</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="editDeviceStatus">Current Status</label>
                                <select class="form-control" id="editDeviceStatus" name="current_status">
                                    <option>In Use</option>
                                    <option>Available</option>
                                    <option>Under Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editQuantity">Quantity</label>
                                <input type="number" class="form-control" id="editQuantity" name="quantity">
                            </div>
                            <div class="col-md-4">
                                <label for="editDevicePR">PR</label>
                                <input type="text" class="form-control" id="editDevicePR" name="pr">
                            </div>

                            <div class="col-md-12">
                                <label for="borrwer">Borrower (Optional)</label>
                                <textarea name="borrower" id="editBorrower" class="form-control" cols="30" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="refresh_web_browser()">Cancel</button>
                <button type="submit" form="editDeviceForm" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="filterSearchToPrintModal" tabindex="-1" aria-labelledby="filterSearchToPrintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="filterSearchToPrintModalLabel">
                    <i class="bi bi-funnel"></i> Filter and Print Records
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="printFilterForm">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="filterCondition">Condition</label>
                            <select class="form-control" id="filterCondition" name="condition">
                                <option value="">All</option>
                                <option>New</option>
                                <option>Good</option>
                                <option>Needs Repair</option>
                                <option>Damaged</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="filterStatus">Status</label>
                            <select class="form-control" id="filterStatus" name="status">
                                <option value="">All</option>
                                <option>In Use</option>
                                <option>Available</option>
                                <option>Under Maintenance</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="filterCategory">Category</label>
                            <input type="text" class="form-control" id="filterCategory" name="category" placeholder="Enter category">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="filterFrom">From Date</label>
                            <input type="date" class="form-control" id="filterFrom" name="from_date">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="filterTo">To Date</label>
                            <input type="date" class="form-control" id="filterTo" name="to_date">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="bi bi-x"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="applyPrintFilter">
                    <i class="bi bi-printer"></i> Print Records
                </button>
            </div>

        </div>
    </div>
</div>


<script>
const createDeviceForm = document.getElementById('createDeviceForm');
const createDeviceModal = document.getElementById('createDeviceModal');
const openModalBtn = document.querySelector('[data-target="#createDeviceModal"]');
const closeModalBtns = createDeviceModal.querySelectorAll('[data-dismiss="modal"]');

function showModal(modal) {
    modal.classList.add('show');
    modal.style.display = 'block';
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');

    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    document.body.appendChild(backdrop);
}

function hideModal(modal) {
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');

    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
}

openModalBtn.addEventListener('click', () => showModal(createDeviceModal));
closeModalBtns.forEach(btn => btn.addEventListener('click', () => hideModal(createDeviceModal)));
createDeviceModal.addEventListener('click', (e) => { if (e.target === createDeviceModal) hideModal(createDeviceModal); });


createDeviceForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        usrid: '<?= $_SESSION['user_id'] ?? '' ?>',
        role: '<?= $_SESSION['role'] ?? '' ?>',
        name: document.getElementById('deviceName').value,
        brand: document.getElementById('deviceBrand').value,
        model: document.getElementById('deviceModel').value,
        serialNum: document.getElementById('deviceSerial').value,
        category: document.getElementById('deviceCategory').value,
        condition: document.getElementById('deviceCondition').value,
        current_status: document.getElementById('deviceStatus').value,
        quantity: parseInt(document.getElementById('quantity').value),
        borrower: document.getElementById('borrower').value,
        pr: document.getElementById('devicePR').value,
        action: 'additem'
    };

    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message || 'Device added successfully',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                hideModal(createDeviceModal);
                createDeviceForm.reset();
                location.reload(); 
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.error || 'Failed to add device',
            });
        }

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'An unexpected error occurred.',
        });
    }
});


const editDeviceModal = document.getElementById('editDeviceModal');
const editDeviceForm = document.getElementById('editDeviceForm');

document.querySelectorAll('.editDeviceBtn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        const id = btn.dataset.id;
        const row = btn.closest('tr');

        document.getElementById('editDeviceId').value = id;
        document.getElementById('editDeviceName').value = row.cells[0].textContent.trim();
        document.getElementById('editDeviceModel').value = row.cells[1].textContent.trim();
        document.getElementById('editDeviceBrand').value = row.cells[2].textContent.trim();
        document.getElementById('editDeviceSerial').value = row.cells[3].textContent.trim();
        document.getElementById('editDeviceCategory').value = row.cells[4].textContent.trim();
        document.getElementById('editDeviceCondition').value = row.cells[5].textContent.trim();
        document.getElementById('editDeviceStatus').value = row.cells[6].textContent.trim();
        document.getElementById('editQuantity').value = row.cells[8].textContent.trim();
        document.getElementById('editDevicePR').value = row.cells[7].textContent.trim();
        document.getElementById('editBorrower').value = row.cells[9].textContent.trim();

        showModal(editDeviceModal);
    });
});

editDeviceModal.addEventListener('click', (e) => { if (e.target === editDeviceModal) hideModal(editDeviceModal); });

editDeviceForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        d_uid: document.getElementById('editDeviceId').value,
        name: document.getElementById('editDeviceName').value,
        model: document.getElementById('editDeviceModel').value,
        brand: document.getElementById('editDeviceBrand').value,
        serialNum: document.getElementById('editDeviceSerial').value,
        category: document.getElementById('editDeviceCategory').value,
        device_condition: document.getElementById('editDeviceCondition').value,
        current_status: document.getElementById('editDeviceStatus').value,
        quantity: parseInt(document.getElementById('editQuantity').value),
        borrower: document.getElementById('editBorrower').value,
        pr: document.getElementById('editDevicePR').value,
        action: 'edititem'
    };

    try {
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message || 'Device updated successfully',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                hideModal(editDeviceModal);
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.error || 'Failed to update device',
            });
        }

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'An unexpected error occurred.',
        });
    }
});


document.querySelectorAll('.archiveDeviceBtn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const d_uid = btn.dataset.id;
        const usrid = '<?= $_SESSION['user_id'] ?? '' ?>';
        const role = '<?= $_SESSION['role'] ?? '' ?>';

        Swal.fire({
            title: 'Move to Archive?',
            text: "The device will be archived.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it!',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch('api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            d_uid,
                            usrid,
                            role,
                            action: 'archiveitem'
                        })
                    });

                    const resultJson = await response.json();

                    if (response.ok && resultJson.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archived!',
                            text: resultJson.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: resultJson.error || 'Failed to archive device.'
                        });
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Unexpected error occurred.'
                    });
                }
            }
        });
    });
});

const searchInput = document.getElementById('deviceSearch');

function doSearch() {
    const query = searchInput.value.trim();
    const url = new URL(window.location.href);
    url.searchParams.set('search', query);
    url.searchParams.set('p', 1); 
    window.location.href = url.toString();
}

searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') doSearch();
});


document.getElementById('search-addon').addEventListener('click', doSearch);

function refresh_web_browser() {
    location.reload();
}


document.getElementById('applyPrintFilter').addEventListener('click', () => {
    const form = document.getElementById('printFilterForm');
    const formData = new FormData(form);
    const queryParams = new URLSearchParams(formData).toString();

    window.open(`api/p_r.php?${queryParams}`, '_blank');
});

const searchInputs = document.getElementById('deviceSearch');
const tableRows = document.querySelectorAll('table tbody tr');

searchInput.addEventListener('keyup', () => {
    const searchValue = searchInputs.value.toLowerCase().trim();

    tableRows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        row.style.display = rowText.includes(searchValue) ? '' : 'none';
    });
});


</script>
