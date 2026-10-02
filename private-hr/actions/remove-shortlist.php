<?php

session_start();

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../public/company/employer-auth.php");

$company_id = (int)($_SESSION['company_id'] ?? 0);

$shortlist_id = $_POST['shortlist_id'] ?? null;

if (!$company_id || !$shortlist_id) {
    header("Location: /tamkeentest/public/hr/shortlist.php");
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM hr_shortlists
    WHERE id = ?
    AND company_id = ?
");

$stmt->execute([
    $shortlist_id,
    $company_id
]);

header("Location: /tamkeentest/public/hr/shortlist.php");
exit;
