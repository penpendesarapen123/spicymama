<?php

require_once __DIR__.'/../_init.php';

// Delete discount
if (get('action') === 'delete') {
    $id = get('id');
    $discount = Discount::find($id);

    if ($discount) {
        $discount->delete();
        flashMessage('delete_discount', 'Discount deleted successfully', FLASH_SUCCESS);
    } else {
        flashMessage('delete_discount', 'Invalid discount', FLASH_ERROR);
    }
    redirect('../admin_discount.php');
}

// Add discount
if (post('action') === 'add') {
    $name = post('name');
    $percentage = post('percentage');
    $start_date = post('start_date');
    $end_date = post('end_date');

    try {
        Discount::add($name, $percentage, $start_date, $end_date);
        flashMessage('add_discount', 'Discount added successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('add_discount', $ex->getMessage(), FLASH_ERROR);
    }

    redirect('../admin_discount.php');
}

// Update discount
if (post('action') === 'update') {
    $id = post('id');
    $name = post('name');
    $percentage = post('percentage');
    $start_date = post('start_date');
    $end_date = post('end_date');

    try {
        $discount = Discount::find($id);
        if ($discount) {
            $discount->discount_name = $name;
            $discount->discount_percentage = $percentage;
            $discount->start_date = $start_date;
            $discount->end_date = $end_date;
            $discount->update();
            flashMessage('update_discount', 'Discount updated successfully.', FLASH_SUCCESS);
            redirect('../admin_discount.php');
        } else {
            flashMessage('update_discount', 'Invalid discount', FLASH_ERROR);
            redirect("../admin_discount.php?action=update&id={$id}");
        }
    } catch (Exception $ex) {
        flashMessage('update_discount', $ex->getMessage(), FLASH_ERROR);
        redirect("../admin_discount.php?action=update&id={$id}");
    }
}

?>
