<?php

require_once '_init.php';

class Guard {

    public static function adminAndCashierOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || ($currentUser->role !== ROLE_ADMIN && $currentUser->role !== ROLE_CASHIER)) {
            redirect('login.php');
        }
    }
 
    public static function adminOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || $currentUser->role !== ROLE_ADMIN) {
            redirect('login.php');
        }
    }

    public static function cashierOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || $currentUser->role !== ROLE_CASHIER) {
            redirect('login.php');
        }
    }

    public static function managerOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || $currentUser->role !== ROLE_MANAGER) {
            redirect('login.php');
        }
    }

    // NEW: Allow managers and cashiers access
    public static function managerAndCashierOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || ($currentUser->role !== 'MANAGER' && $currentUser->role !== 'CASHIER')) {
            redirect('login.php');
        }
    }

    public static function adminAndManagerOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || ($currentUser->role !== ROLE_ADMIN && $currentUser->role !== ROLE_MANAGER)) {
            redirect('login.php');
        }
    }

    public static function allRoles()
    {
        $currentUser = User::getAuthenticatedUser();

        if (
            !$currentUser || 
            !in_array($currentUser->role, ['ADMIN', 'MANAGER', 'CASHIER'])
        ) {
            redirect('login.php');
        }
    }

    public static function hasModel($modelClass)
    {
        $model = $modelClass::find(get('id'));

        if ($model == null) {
            header('Content-type: text/plain');
            die('Page not found');
        }

        return $model;
    }

    public static function guestOnly() 
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser) return;

        redirect($currentUser->getHomePage());
    }
    
    public static function adminManagerCashierOnly()
    {
        $currentUser = User::getAuthenticatedUser();
    
        // Redirect to login if the user is not Admin, Manager, or Cashier
        if (!$currentUser || !in_array($currentUser->role, [ROLE_ADMIN, ROLE_MANAGER, ROLE_CASHIER])) {
            redirect('login.php');
        }
    }
}
