<?php
require_once 'db_config/db_conn.php';

$date = $_POST['date'];
$task = $_POST['task'];

// Get task_id and estimated_time from the maintenance_tasks table
$query = "SELECT task_id, estimated_time FROM maintenance_tasks WHERE task_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $task);
$stmt->execute();
$result = $stmt->get_result();
$task_row = $result->fetch_assoc();
$task_id = $task_row['task_id'];
$estimated_time = $task_row['estimated_time'];

// Get all service centers that perform the selected task
$query = "SELECT sc.service_center_id, sc.service_center_name 
          FROM service_centers sc
          WHERE sc.task_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $task_id);
$stmt->execute();
$result = $stmt->get_result();

$service_centers = [];
while ($row = $result->fetch_assoc()) {
    $service_centers[$row['service_center_id']] = [
        'name' => $row['service_center_name'],
        'timeslots' => []
    ];
}

// Get maintenance schedules for the selected date and task
$query = "SELECT schedule_id, service_center_id, schedule_start_time, schedule_end_time, status 
          FROM maintenance_schedule 
          WHERE task_id = ? AND schedule_date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('is', $task_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$occupied_timeslots = [];
while ($row = $result->fetch_assoc()) {
    $schedule_id = $row['schedule_id'];
    $service_center_id = $row['service_center_id'];
    $occupied_timeslots[$service_center_id][$schedule_id] = [
        'start_time' => $row['schedule_start_time'],
        'end_time' => $row['schedule_end_time'],
        'status' => $row['status']
    ];
}

// Define the working hours (example: 08:00 AM - 06:00 PM)
$start_of_day = new DateTime('08:00:00');
$end_of_day = new DateTime('18:00:00');

// Determine available timeslots for each service center
foreach ($service_centers as $service_center_id => $service_center) {
    $current_time = clone $start_of_day;
    
    while ($current_time < $end_of_day) {
        $end_time = clone $current_time;
        $end_time->modify("+{$estimated_time} hours");

        if ($end_time > $end_of_day) {
            break;
        }

        $is_available = true;
        if (isset($occupied_timeslots[$service_center_id])) {
            foreach ($occupied_timeslots[$service_center_id] as $schedule_id => $timeslot) {
                $slot_start = new DateTime($timeslot['start_time']);
                $slot_end = new DateTime($timeslot['end_time']);

                // Check if the current timeslot overlaps with any booked timeslot
                if (
                    ($current_time >= $slot_start && $current_time < $slot_end) ||
                    ($end_time > $slot_start && $end_time <= $slot_end) ||
                    ($current_time <= $slot_start && $end_time >= $slot_end)
                ) {
                    // Exclude the booked timeslot itself from making the current timeslot unavailable
                    if ($timeslot['status'] == 'Scheduled') {
                        $is_available = false;
                        break;
                    }
                }
            }
        }

        $service_centers[$service_center_id]['timeslots'][] = [
            'start_time' => $current_time->format('H:i:s'),
            'end_time' => $end_time->format('H:i:s'),
            'status' => $is_available ? 'available' : 'unavailable'
        ];

        $current_time->modify("+{$estimated_time} hours");
    }
}

// Output the available timeslots for each service center
foreach ($service_centers as $service_center_id => $service_center) {
    echo "<div class='service-center'>";
    echo "<h6><b>{$service_center['name']}</b></h6>";
    echo "<div class='timeslots'>";
    foreach ($service_center['timeslots'] as $timeslot) {
        $status_class = $timeslot['status'] == 'available' ? 'available' : 'unavailable';
        echo "<div class='timeslot {$status_class}' data-service-center-id='{$service_center_id}' data-start-time='{$timeslot['start_time']}' data-end-time='{$timeslot['end_time']}'>{$timeslot['start_time']} to {$timeslot['end_time']}</div>";
    }
    echo "</div></div>";
}
?>
