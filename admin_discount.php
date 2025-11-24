<?php
//Guard
require_once '_guards.php';
Guard::adminAndManagerOnly();

require_once 'api/discount_controller.php';

// Get all discounts
$discounts = Discount::all();

// Get discount for update, if provided
$discountToUpdate = null;
if (get('action') === 'update') {
    $id = get('id');
    $discountToUpdate = Discount::find($id);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Discount</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php require 'templates/admin_header.php' ?>

<div class="flex">
    <?php require 'templates/admin_navbar.php' ?>
    <main>
        <div class="flex">
            <div class="category-form">
                <span class="subtitle">
                    <?php if (get('action') === 'update') : ?>
                        Update Discount
                    <?php else : ?>
                        New Discount
                    <?php endif; ?>
                </span>
                <hr/>

                <div class="card">
                    <div class="card-content">
                        <form method="POST" action="api/discount_controller.php">

                            <input type="hidden" name="action" value="<?= get('action') === 'update' ? 'update' : 'add' ?>" />
                            <input type="hidden" name="id" value="<?= isset($discountToUpdate) ? $discountToUpdate->discount_id : '' ?>" />

                            <div class="form-control">
                                <label>Discount Name</label>
                                <input 
                                    value="<?= isset($discountToUpdate) ? $discountToUpdate->discount_name : '' ?>" 
                                    type="text" 
                                    name="name" 
                                    placeholder="Enter discount name here" 
                                    required="true" 
                                />
                            </div>

                            <div class="form-control">
                                <label>Percentage</label>
                                <input 
                                    value="<?= isset($discountToUpdate) ? $discountToUpdate->discount_percentage : '' ?>" 
                                    type="number" 
                                    name="percentage" 
                                    placeholder="Enter discount percentage here" 
                                    required="true" 
                                />
                            </div>

                            <div class="form-control">
                                <label>Start Date</label>
                                <input 
                                    value="<?= isset($discountToUpdate) ? $discountToUpdate->start_date : '' ?>" 
                                    type="date" 
                                    name="start_date"  
                                />
                            </div>

                            <div class="form-control">
                                <label>End Date</label>
                                <input 
                                    value="<?= isset($discountToUpdate) ? $discountToUpdate->end_date : '' ?>" 
                                    type="date" 
                                    name="end_date"  
                                />
                            </div>

                            <div class="mt-16">
                                <button class="btn btn-primary w-full" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="category-table">
                <span class="subtitle">Discount List</span>
                <hr/>

                <?php displayFlashMessage('add_discount') ?>
                <?php displayFlashMessage('delete_discount') ?>
                <?php displayFlashMessage('update_discount') ?>

                <table id="discountTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Percentage</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($discounts as $discount) : ?>
                        <tr>
                            <td><?= $discount->discount_name ?></td>
                            <td><?= $discount->discount_percentage ?></td>
                            <td><?= $discount->start_date ?></td>
                            <td><?= $discount->end_date ?></td>
                            <td>
                                <a class="text-primary" href="?action=update&id=<?= $discount->discount_id ?>">Update</a>
                                <a class="text-red-500 ml-16" href="api/discount_controller.php?action=delete&id=<?= $discount->discount_id ?>">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
