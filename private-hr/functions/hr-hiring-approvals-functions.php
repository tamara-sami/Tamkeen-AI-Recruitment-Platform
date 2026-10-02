<?php
/**
 * HR Hiring Approvals helper functions.
 * This page is for HR to track approval tickets sent to admin.
 */

if (!function_exists('getHrHiringApprovalPageData')) {
    function getHrHiringApprovalPageData(PDO $conn, int $company_id, array $filters = []) {
        $company = getHrApprovalCompanyInfo($conn, $company_id);
        $companyName = $company['company_name'] ?? 'Company';
        $avatarLetter = strtoupper(substr($companyName, 0, 1));

        return [
            'company' => $company,
            'companyName' => $companyName,
            'avatarLetter' => $avatarLetter,
            'requests' => getHrHiringApprovals($conn, $company_id, $filters),
            'stats' => getHrHiringApprovalStats($conn, $company_id),
            'latestRequest' => getLatestHrHiringApproval($conn, $company_id)
        ];
    }
}

if (!function_exists('getHrApprovalCompanyInfo')) {
    function getHrApprovalCompanyInfo(PDO $conn, int $company_id) {
        $stmt = $conn->prepare("SELECT company_name, company_logo FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('getHrHiringApprovals')) {
    function getHrHiringApprovals(PDO $conn, int $company_id, array $filters = []) {
        $params = [$company_id];

        $query = "
            SELECT
                hr.*,
                hr.admin_note,
                js.full_name,
                js.email,
                js.mobile,
                js.job_title,
                js.profile_image,
                jsp.location,
                i.interview_date,
                i.interview_time,
                i.interview_type,
                i.interviewer_name,
                i.interviewer_phone,
                i.location_or_link
            FROM hiring_requests hr
            JOIN job_seekers js ON js.id = hr.job_seeker_id
            LEFT JOIN job_seeker_profiles jsp ON jsp.job_seeker_id = js.id
            LEFT JOIN interviews i ON i.id = hr.interview_id
            WHERE hr.company_id = ?
        ";

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query .= " AND hr.status = ? ";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query .= " AND hr.hiring_type = ? ";
            $params[] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $query .= "
                AND (
                    js.full_name LIKE ?
                    OR js.email LIKE ?
                    OR js.job_title LIKE ?
                    OR js.mobile LIKE ?
                    OR i.interviewer_name LIKE ?
                )
            ";
            $term = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term, $term, $term);
        }

        $query .= "
            ORDER BY
                CASE hr.status
                    WHEN 'pending' THEN 1
                    WHEN 'approved' THEN 2
                    WHEN 'rejected' THEN 3
                    ELSE 4
                END,
                hr.created_at DESC
        ";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('getHrHiringApprovalStats')) {
    function getHrHiringApprovalStats(PDO $conn, int $company_id) {
        $stmt = $conn->prepare("
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'pending'), 0) AS pending,
                COALESCE(SUM(status = 'approved'), 0) AS approved,
                COALESCE(SUM(status = 'rejected'), 0) AS rejected
            FROM hiring_requests
            WHERE company_id = ?
        ");
        $stmt->execute([$company_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];
    }
}

if (!function_exists('getLatestHrHiringApproval')) {
    function getLatestHrHiringApproval(PDO $conn, int $company_id) {
        $stmt = $conn->prepare("
            SELECT hr.*, js.full_name
            FROM hiring_requests hr
            JOIN job_seekers js ON js.id = hr.job_seeker_id
            WHERE hr.company_id = ?
            ORDER BY hr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('approvalStatusClass')) {
    function approvalStatusClass($status) {
        return [
            'pending' => 'pending',
            'approved' => 'approved',
            'rejected' => 'rejected'
        ][$status] ?? 'pending';
    }
}

if (!function_exists('approvalStatusLabel')) {
    function approvalStatusLabel($status) {
        return [
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ][$status] ?? 'Pending Review';
    }
}

if (!function_exists('hiringTypeLabel')) {
    function hiringTypeLabel($type) {
        return ucwords(str_replace('_', ' ', (string)$type));
    }
}

if (!function_exists('formatApprovalDate')) {
    function formatApprovalDate($date) {
        $timestamp = strtotime((string)$date);
        if (!$timestamp) return 'Date not set';
        return date('M d, Y · h:i A', $timestamp);
    }
}
