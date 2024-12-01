<?php
include "config/db.php"; // Include your fixed db.php file
header("Content-Type: application/json");

// Get the HTTP request method
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Get the task ID if provided in the URL
$task_id = isset($_GET["id"]) ? intval($_GET["id"]) : null;

// API Routes
switch ($requestMethod) {
    case 'POST': // Create a new task
        createTask();
        break;

    case 'GET': // Get tasks (all or by ID)
        if ($task_id) {
            getTask($task_id); // Get a single task by ID
        } else {
            getTasks(); // Get all tasks
        }
        break;

    case 'PUT': // Update a task by ID
    case 'PATCH':
        if ($task_id) {
            updateTask($task_id);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Task ID is required for update"]);
        }
        break;

    case 'DELETE': // Delete task(s)
        if ($task_id) {
            deleteTask($task_id); // Delete a single task
        } else {
            deleteAllTasks(); // Delete all tasks
        }
        break;

    default: // Invalid method
        http_response_code(405); // Method Not Allowed
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

mysqli_close($connected); // Close the database connection


// Function to create a task
function createTask() {
    global $connected;

    $data = json_decode(file_get_contents("php://input"), true);

    $title = mysqli_real_escape_string($connected, $data['title'] ?? '');
    $description = mysqli_real_escape_string($connected, $data['description'] ?? '');

    if (!empty($title)) {
        $sql = "INSERT INTO tasks (title, description) VALUES ('$title', '$description')";
        if (mysqli_query($connected, $sql)) {
            http_response_code(201); // Created
            echo json_encode(["message" => "Task created successfully", "id" => mysqli_insert_id($connected)]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "Error creating task: " . mysqli_error($connected)]);
        }
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Title is required"]);
    }
}

// Function to get all tasks
function getTasks() {
    global $connected;

    $sql = "SELECT * FROM tasks"; // Ensure the table name is correct
    $result = mysqli_query($connected, $sql);

    if ($result) {
        $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($tasks);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Error fetching tasks: " . mysqli_error($connected)]);
    }
}

// Function to get a single task by ID
function getTask($id) {
    global $connected;

    $id = intval($id);
    $sql = "SELECT * FROM tasks WHERE id = $id";
    $result = mysqli_query($connected, $sql);

    if ($result) {
        $task = mysqli_fetch_assoc($result);
        if ($task) {
            echo json_encode($task);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["message" => "Task not found"]);
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Error fetching task: " . mysqli_error($connected)]);
    }
}

// Function to update a task by ID
function updateTask($id) {
    global $connected;

    $data = json_decode(file_get_contents("php://input"), true);

    $title = mysqli_real_escape_string($connected, $data['title'] ?? null);
    $description = mysqli_real_escape_string($connected, $data['description'] ?? null);

    if ($title || $description) {
        $updates = [];
        if ($title) $updates[] = "title = '$title'";
        if ($description) $updates[] = "description = '$description'";

        $sql = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE id = $id";
        if (mysqli_query($connected, $sql)) {
            echo json_encode(["message" => "Task updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error updating task: " . mysqli_error($connected)]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "At least one field (title or description) is required"]);
    }
}

// Function to delete a task by ID
function deleteTask($id) {
    global $connected;

    $sql = "DELETE FROM tasks WHERE id = $id";
    if (mysqli_query($connected, $sql)) {
        echo json_encode(["message" => "Task deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting task: " . mysqli_error($connected)]);
    }
}

// Function to delete all tasks
function deleteAllTasks() {
    global $connected;

    $sql = "DELETE FROM tasks";
    if (mysqli_query($connected, $sql)) {
        echo json_encode(["message" => "All tasks deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting all tasks: " . mysqli_error($connected)]);
    }
}
?>
