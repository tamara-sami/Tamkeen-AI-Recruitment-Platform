<?php
session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

if (!isset($_GET['id'])) {
    header("Location: ../../public/training/archived-trainings.php");
    exit;
}

$training_id = $_GET['id'];
$company_id = $_SESSION['company_id'];

$stmt = $conn->prepare("
    UPDATE trainings 
    SET deleted = 0
    WHERE id = ? AND company_id = ?
");

$stmt->execute([$training_id, $company_id]);

header("Location: ../../public/training/archived-trainings.php");
exit;
