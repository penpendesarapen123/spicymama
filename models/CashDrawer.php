<?php

require_once __DIR__ . '/../_init.php';

class CashDrawer
{
    /**
     * Get the current balance of the cash drawer.
     * 
     * @return float The current cash drawer balance.
     */
    public static function getBalance()
    {
        global $connection;

        $query = "SELECT current_balance FROM cash_drawer LIMIT 1";
        $stmt = $connection->prepare($query);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    /**
     * Update the cash drawer balance.
     * 
     * @param float $newBalance The new balance to be set.
     * @return bool True on success, false on failure.
     */
    public static function updateBalance($newBalance)
    {
        global $connection;

        $query = "UPDATE cash_drawer SET current_balance = ?, last_updated = NOW() WHERE id = 1";
        $stmt = $connection->prepare($query);

        return $stmt->execute([$newBalance]);
    }

    /**
     * Initialize the cash drawer with a starting balance.
     * 
     * @param float $initialBalance The initial cash drawer amount.
     * @return bool True on success, false on failure.
     */
    public static function initialize($initialBalance)
    {
        global $connection;

        $query = "INSERT INTO cash_drawer (current_balance) VALUES (?)";
        $stmt = $connection->prepare($query);

        return $stmt->execute([$initialBalance]);
    }
}
