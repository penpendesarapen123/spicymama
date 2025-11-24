<?php

function dd($data) {
    header('Content-type: application/json');
    echo json_encode($data);
    die();
}

function get($key) {
    if (isset($_GET[$key])) return trim($_GET[$key]);
    return "";
}

function post($key) {
    if (isset($_POST[$key])) {
        return trim($_POST[$key]);
    }
    return "";
}

function redirect($url) {
    header("Location: $url", true, 303); 
    exit();
}
 
function flashMessage($key, $message, $type = 'error') {
    session_start(); // Ensure the session is started
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];

    error_log("Flash message set: Key = $key, Message = $message, Type = $type"); // Debug
}

function formattedFlashMessage($flashMessage) {
    return sprintf("<div class='alert alert-%s'>%s</div>",
        $flashMessage['type'],
        $flashMessage['message']
    );
}

function displayFlashMessage($name) {
    if (!isset($_SESSION['flash'][$name])) return;

    $flash = $_SESSION['flash'][$name];
    $message = htmlspecialchars($flash['message']);
    $type = htmlspecialchars($flash['type']);

    // Display the message and remove it from the session
    echo "<div class='alert alert-$type'>$message</div>";
    unset($_SESSION['flash'][$name]);

    error_log("Flash messages in session after display: " . print_r($_SESSION['flash'], true));

}

function setFlashMessage($key, $message) {
    $_SESSION[$key] = $message;
}

function getFlashMessage($key) {
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}
?>
