<?php

require_once __DIR__.'/../_init.php';

// Delete category
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$id) {
        flashMessage('delete_category', 'Invalid category ID.', 'danger');
        redirect('../admin_category.php');
    }

    $category = Category::find($id);

    if ($category) {
        $category->delete();
        flashMessage('delete_category', 'Category deleted successfully.', FLASH_SUCCESS);
    } else {
        flashMessage('delete_category', 'Invalid category.', 'danger');
    }
    redirect('../admin_category.php');
}

// Add new category
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;

    if (!$name) {
        flashMessage('add_category', 'Category name is required.', 'danger');
        redirect('../admin_category.php');
    }

    try {
        // Check if the category already exists
        $existingCategory = Category::findByName($name);
        if ($existingCategory) {
            throw new Exception('Category name already exists.');
        }

        // Add the new category
        Category::add($name);

        flashMessage('add_category', 'Category added successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('add_category', $ex->getMessage(), 'danger');
    }

    redirect('../admin_category.php');
}

// Update category
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;

    if (!$id || !$name) {
        flashMessage('update_category', 'Both ID and name are required.', 'danger');
        redirect('../admin_category.php');
    }

    try {
        $category = Category::find($id); // Find the existing category by ID
        if ($category) {
            $category->name = $name; // Update the name
            $category->update(); // Save changes to DB
            flashMessage('update_category', 'Category updated successfully.', FLASH_SUCCESS);
        } else {
            flashMessage('update_category', 'Category not found.', 'danger');
        }
    } catch (Exception $ex) {
        flashMessage('update_category', $ex->getMessage(), 'danger');
    }

    redirect('../admin_category.php');
}

// Restore category
if (isset($_GET['action']) && $_GET['action'] === 'restore') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$id) {
        flashMessage('restore_category', 'Invalid category ID.', 'danger');
        redirect('../admin_category.php');
    }

    $stmt = $connection->prepare('UPDATE categories SET deleted_at = NULL WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    flashMessage('restore_category', 'Category restored successfully!', FLASH_SUCCESS);
    redirect('../admin_category.php');
}

?>
