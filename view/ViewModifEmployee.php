<?php
/**
 * Employee Modification View
 * 
 * Provides a form interface for modifying existing employees:
 * - Handles employee data fields (name, email, role)
 * - Supports password changes (optional)
 * - Includes store assignment dropdown for IT admins
 * 
 * @package BikeStore\Views
 * @author Your Name
 * @version 1.0
 */

/**
 * Include header template
 */
include_once('www/header.inc.php');

/**
 * @var array $employee Employee data to modify
 * @var array $stores Available stores for assignment
 * @var bool $isIT Whether current user is IT admin
 */
$employee = $GLOBALS['employee_data'];
$stores = $GLOBALS['stores_data'] ?? [];
$isIT = isset($_SESSION['employee']) && $_SESSION['employee']['employee_role'] === 'it';

?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col text-center">
            <h1 class="fw-bold">Modify Employee</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/SAE401/updateemployees'); ?>" method="POST">
                        <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee['employee_id'] ?? ''); ?>">
                        
                        <div class="mb-3">
                            <label for="employee_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="employee_name" name="employee_name" 
                                value="<?php echo htmlspecialchars($employee['employee_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="employee_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="employee_email" name="employee_email" 
                                value="<?php echo htmlspecialchars($employee['employee_email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="employee_password" class="form-label">Password (leave empty to keep current)</label>
                            <input type="password" class="form-control" id="employee_password" name="employee_password">
                            <small class="form-text text-muted">Leave blank to keep current password</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="employee_role" class="form-label">Role</label>
                            <select class="form-select" id="employee_role" name="employee_role" required>
                                <option value="employee" <?php echo (($employee['employee_role'] ?? '') === 'employee') ? 'selected' : ''; ?>>Employee</option>
                                <?php if ($isIT): ?>
                                    <option value="chief" <?php echo (($employee['employee_role'] ?? '') === 'chief') ? 'selected' : ''; ?>>Chief</option>
                                    <option value="it" <?php echo (($employee['employee_role'] ?? '') === 'it') ? 'selected' : ''; ?>>IT Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <?php if ($isIT): ?>
                        <div class="mb-3">
                            <label for="store_id" class="form-label">Store</label>
                            <select class="form-select" id="store_id" name="store_id" required>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?php echo $store['store_id']; ?>" 
                                        <?php echo (($employee['store_id'] ?? '') == $store['store_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($store['store_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                            <input type="hidden" name="store_id" value="<?php echo htmlspecialchars($employee['store_id'] ?? $_SESSION['employee']['store_id'] ?? ''); ?>">
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/SAE401/employees" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include_once('www/footer.inc.php');
?>