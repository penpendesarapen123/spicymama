<?php
require_once __DIR__ . '/../_init.php'; // Include the initialization file for database connection, etc.


class ReturnItemController
{
    public static function returnItem($transactionId, $itemId, $returnQuantity)
    {
        global $connection;

        try {
            // Begin a transaction
            $connection->beginTransaction();

            // Fetch the transaction using OrderItem::all
            $transactions = OrderItem::all(); // Fetch all transactions
            $orderItem = null;

            // Find the specific transaction by ID and product code
            foreach ($transactions as $transaction) {
                if ($transaction->id == $transactionId && $transaction->product_id == $itemId) {
                    $orderItem = $transaction;
                    break;
                }
            }

            if (!$orderItem) {
                throw new Exception('Transaction not found.');
            }

            // Validate return quantity
            if ($returnQuantity > $orderItem->quantity) {
                throw new Exception('Return quantity exceeds purchased quantity.');
            }

            // Update the product stock in the database
            $stmt = $connection->prepare('
                UPDATE products 
                SET quantity = quantity + :returnQuantity 
                WHERE id = :product_id
            ');
            $stmt->execute([
                'returnQuantity' => $returnQuantity,
                'id' => $itemId
            ]);

            // Adjust or remove the order item in the database
            if ($returnQuantity == $orderItem->quantity) {
                // If the entire quantity is returned, delete the order item
                $stmt = $connection->prepare('DELETE FROM order_items WHERE id = :id');
                $stmt->execute(['id' => $transactionId]);
            } else {
                // Reduce the quantity if only part of the order is returned
                $stmt = $connection->prepare('
                    UPDATE order_items 
                    SET quantity = quantity - :returnQuantity 
                    WHERE id = :id
                ');
                $stmt->execute([
                    'returnQuantity' => $returnQuantity,
                    'id' => $transactionId
                ]);
            }

            // Log the return action
            $stmt = $connection->prepare('
                INSERT INTO void_logs (
                    transaction_number,
                    product_code,
                    return_quantity,
                    returned_at
                ) VALUES (
                    :transactionNumber,
                    :itemId,
                    :returnQuantity,
                    NOW()
                )
            ');
            $stmt->execute([
                'transactionNumber' => $orderItem->transaction_number,
                'itemId' => $itemId,
                'returnQuantity' => $returnQuantity
            ]);

            // Commit the transaction
            $connection->commit();

            return [
                'success' => true,
                'message' => 'Item returned successfully and logged.'
            ];
        } catch (Exception $e) {
            // Roll back the transaction if an error occurs
            $connection->rollBack();
            return [
                'success' => false,
                'message' => 'Error during return: ' . $e->getMessage()
            ];
        }
    }
}

