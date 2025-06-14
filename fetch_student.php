<?php
include '../connection/config.php';
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Check if students_data table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'students_data'");
    if ($tableCheck->rowCount() === 0) {
        error_log("students_data table does not exist");
        ob_clean();
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'students_data table not found']);
        exit;
    }

    $query = "SELECT id, first_name, middle_name, last_name, stud_section AS section 
              FROM students_data 
              WHERE CONCAT(last_name, ', ', first_name, ' ', COALESCE(middle_name, ''), ' - ', stud_section) LIKE :search
              ORDER BY last_name, first_name, COALESCE(middle_name, '')
              LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $conn->prepare("SELECT COUNT(*) FROM students_data WHERE CONCAT(last_name, ', ', first_name, ' ', COALESCE(middle_name, ''), ' - ', stud_section) LIKE :search");
    $countStmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $countStmt->execute();
    $totalResults = $countStmt->fetchColumn();
    $more = ($offset + $limit) < $totalResults;

    error_log("fetch_students.php: Found " . count($results) . " students for search '$search'");
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'results' => $results,
        'pagination' => ['more' => $more]
    ]);
} catch (PDOException $e) {
    error_log("Database error in fetch_students.php: " . $e->getMessage());
    ob_clean();
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    error_log("General error in fetch_students.php: " . $e->getMessage());
    ob_clean();
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Server error']);
}
exit;
?>