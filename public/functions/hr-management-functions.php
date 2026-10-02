<?php

function getHrManagementData($conn, $company_id)
{
    return [
        "stats" => getHrManagementStats($conn, $company_id),
        "activities" => getHrActivities($conn, $company_id),
        "tickets" => getHiringTickets($conn, $company_id)
    ];
}

function getHrManagementStats($conn, $company_id)
{
    return [
        "hrUsers" => countTable($conn, "users", $company_id, "role = 'hr'"),

        "emailsSent" => countTable(
            $conn,
            "hr_shortlists",
            $company_id,
            "status IN ('waiting_reply','replied')"
        ),

        "shortlisted" => countTable(
            $conn,
            "hr_shortlists",
            $company_id
        ),

        "hiringOffers" => countTable(
            $conn,
            "hiring_requests",
            $company_id
        ),

        "pendingTickets" => countTable(
            $conn,
            "hiring_requests",
            $company_id,
            "status = 'pending'"
        ),

        "qrScans" => 0
    ];
}

function countTable($conn, $table, $company_id, $extra = "")
{
    $sql = "SELECT COUNT(*) FROM $table WHERE company_id = ?";

    if ($extra !== "") {
        $sql .= " AND $extra";
    }

    $stmt = $conn->prepare($sql);

    $stmt->execute([$company_id]);

    return (int)$stmt->fetchColumn();
}

function getHrActivities($conn, $company_id)
{
    $stmt = $conn->prepare("

        (
            SELECT 
                'fa fa-star' AS icon,

                'Candidate shortlisted' AS title,

                CONCAT(
                    js.full_name,
                    ' was added to shortlist.'
                ) AS description,

                hs.created_at

            FROM hr_shortlists hs

            JOIN job_seekers js
            ON js.id = hs.job_seeker_id

            WHERE hs.company_id = ?
        )

        UNION ALL

        (
            SELECT
                'fa fa-calendar-check' AS icon,

                'Interview scheduled' AS title,

                CONCAT(
                    js.full_name,
                    ' interview was added.'
                ) AS description,

                i.created_at

            FROM interviews i

            JOIN job_seekers js
            ON js.id = i.job_seeker_id

            WHERE i.company_id = ?
        )

        UNION ALL

        (
            SELECT
                'fa fa-ticket-alt' AS icon,

                'Hiring request submitted' AS title,

                CONCAT(
                    js.full_name,
                    ' was sent for admin approval.'
                ) AS description,

                hr.created_at

            FROM hiring_requests hr

            JOIN job_seekers js
            ON js.id = hr.job_seeker_id

            WHERE hr.company_id = ?
        )

        ORDER BY created_at DESC

        LIMIT 3
    ");

    $stmt->execute([
        $company_id,
        $company_id,
        $company_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getHiringTickets($conn, $company_id)
{
    $stmt = $conn->prepare("

        SELECT 
            hr.*,

            js.full_name,
            js.email,
            js.job_title,
            js.mobile

        FROM hiring_requests hr

        JOIN job_seekers js
        ON js.id = hr.job_seeker_id

        WHERE hr.company_id = ?

        ORDER BY 
            CASE hr.status
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4
            END,

            hr.created_at DESC

        LIMIT 3
    ");

    $stmt->execute([$company_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateHiringRequestStatus(
    $conn,
    $request_id,
    $company_id,
    $status,
    $admin_note = null
) {

    if (!in_array($status, ['approved', 'rejected'])) {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE hiring_requests

        SET
            status = ?,
            admin_note = ?

        WHERE id = ?
        AND company_id = ?
    ");

    return $stmt->execute([
        $status,
        trim($admin_note ?? ''),
        $request_id,
        $company_id
    ]);
}

function hrManagementDate($date)
{
    return empty($date)
        ? "—"
        : date("M d, Y", strtotime($date));
}

function hiringTypeText($type)
{
    return ucwords(
        str_replace("_", " ", $type)
    );
}

function ticketStatusClass($status)
{
    return [
        "pending" => "warning",
        "approved" => "success",
        "rejected" => "danger"
    ][$status] ?? "secondary";
}


function renderCompanyLogo($company_name, $company_logo, $class = "profile-avatar")
{
    if (!empty($company_logo)) {

        return '
            <img
                src="../../public/uploads/company/' . htmlspecialchars($company_logo) . '"
                class="' . htmlspecialchars($class) . '"
                style="object-fit:cover;"
                alt="Company Logo">
        ';
    }

    return '
        <div class="' . htmlspecialchars($class) . '">
            ' . htmlspecialchars(strtoupper(substr($company_name, 0, 1))) . '
        </div>
    ';
}