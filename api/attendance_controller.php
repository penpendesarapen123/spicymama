<?php

require_once __DIR__.'/../_init.php';

date_default_timezone_set('Asia/Manila'); // Set the timezone to Philippines

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (post('name') && post('action')) {
        $action = post('action');

        try {
            $currentDateTime = new DateTime();
            $currentDate = $currentDateTime->format('Y-m-d');
            $currentTime = $currentDateTime->format('H:i:s'); // Ensure correct time format

            if ($action === 'timein') {
                Attendance::create(post('name'), $currentTime, $currentDate);
                setFlashMessage('add_attendance', 'Time in recorded successfully.');
            } elseif ($action === 'timeout') {
                $attendance = Attendance::getLastRecordByName(post('name'));
                if ($attendance && !$attendance->timeout) {
                    Attendance::updateTimeout($attendance->id, $currentTime);
                    setFlashMessage('add_attendance', 'Time out recorded successfully.');
                } else {
                    setFlashMessage('add_attendance', 'No matching time in found or already timed out.');
                }
            }
        } catch (Exception $e) {
            setFlashMessage('add_attendance', 'Failed to record attendance: ' . $e->getMessage());
        }
    } else {
        setFlashMessage('add_attendance', 'All fields are required.');
    }
    header('Location: ../admin_attendance.php');
    exit();
}
?>
