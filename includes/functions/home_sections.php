<?php

function fetchRows($conn, $sql, $params = [])
{
    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function fetchOne($conn, $sql, $params = [])
{
    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function getHomeChallenges($conn, $limit = 3)
{
    return fetchRows($conn, "
        SELECT id, title, description, category, difficulty, estimated_time, points, required_skills
        FROM tasks
        WHERE status = 'published' AND deleted = 0
        ORDER BY created_at DESC
        LIMIT :limit
    ", [':limit' => (int)$limit]);
}

function getHomeTraining($conn, $limit = 3)
{
    return fetchRows($conn, "
        SELECT
            t.id,
            t.title,
            t.description,
            t.training_type,
            t.field,
            t.location,
            t.duration,
            t.start_date,
            t.end_date,
            t.required_skills,
            t.seats,
            c.company_name
        FROM trainings t
        LEFT JOIN companies c ON c.id = t.company_id
        WHERE t.status = 'published' AND t.deleted = 0
        ORDER BY t.created_at DESC
        LIMIT :limit
    ", [':limit' => (int)$limit]);
}

function isJobSeekerLoggedIn()
{
    return !empty($_SESSION['job_seeker_id'])
        || (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'user')
        || (!empty($_SESSION['role']) && $_SESSION['role'] === 'user');
}

function homeTrainingLink($training)
{
    if (isJobSeekerLoggedIn()) {
        $id = (int)($training['id'] ?? 0);
        $title = urlencode((string)($training['title'] ?? ''));
        return "user/trainings.php?search={$title}#training-{$id}";
    }

    return "user/login.php";
}

function homeTrainingType($training)
{
    return $training['training_type'] ?? $training['field'] ?? 'Training';
}

function homeTrainingMeta($training)
{
    $field = trim((string)($training['field'] ?? ''));
    $duration = trim((string)($training['duration'] ?? ''));
    $location = trim((string)($training['location'] ?? ''));

    $parts = array_filter([$field, $duration, $location]);
    return !empty($parts) ? implode(' · ', $parts) : 'Training Opportunity';
}

function homeTrainingBadge($training)
{
    if (!empty($training['seats'])) {
        return (int)$training['seats'] . ' Seats';
    }

    return 'Training';
}

function getCommunityCardsFromTasks($conn, $limit = 3)
{
    return fetchRows($conn, "
        SELECT category, required_skills, difficulty, points
        FROM tasks
        WHERE status = 'published' AND deleted = 0
          AND category <> '' AND required_skills <> ''
        ORDER BY created_at DESC
        LIMIT :limit
    ", [':limit' => (int)$limit]);
}

function getCommunityStatsFromTasks($conn)
{
    $stats = fetchOne($conn, "
        SELECT
            COUNT(*) AS active_courses,
            COUNT(DISTINCT category) AS categories_count,
            COALESCE(SUM(points), 0) AS total_points,
            COALESCE(ROUND(AVG(points)), 0) AS avg_points
        FROM tasks
        WHERE status = 'published' AND deleted = 0
    ");

    if (!$stats) {
        return [
            'active_courses' => 0,
            'categories_count' => 0,
            'total_points' => 0,
            'avg_points' => 0
        ];
    }

    return $stats;
}

function firstSkill($skills)
{
    $parts = array_map('trim', explode(',', (string)$skills));
    return $parts[0] ?? 'Skill';
}

function secondSkill($skills)
{
    $parts = array_map('trim', explode(',', (string)$skills));
    return $parts[1] ?? ($parts[0] ?? 'Practice');
}

function safe($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


function smartSearchTerms($query)
{
    $query = trim((string)$query);
    $terms = [$query];

    $map = [
        'frontend' => ['front end', 'html', 'css', 'javascript', 'react', 'ui'],
        'front-end' => ['frontend', 'html', 'css', 'javascript', 'react', 'ui'],
        'design' => ['ui', 'ux', 'figma', 'wireframe', 'mockup', 'layout'],
        'data' => ['analysis', 'analytics', 'excel', 'sql', 'dashboard', 'report'],
        'programming' => ['coding', 'php', 'javascript', 'html', 'css', 'software'],
        'marketing' => ['social media', 'content', 'campaign', 'copywriting'],
        'internship' => ['training', 'voluntary', 'university', 'trainee'],
        'training' => ['internship', 'bootcamp', 'course', 'learning'],
        'qa' => ['testing', 'test cases', 'quality assurance'],
    ];

    $lower = strtolower($query);
    foreach ($map as $key => $synonyms) {
        if (str_contains($lower, $key)) {
            $terms = array_merge($terms, $synonyms);
        }
    }

    $words = preg_split('/\s+/', $query);
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) >= 3) {
            $terms[] = $word;
        }
    }

    return array_values(array_unique(array_filter($terms)));
}

function buildSmartWhere($columns, $terms, &$params)
{
    $parts = [];
    foreach ($terms as $i => $term) {
        $param = ':term' . count($params);
        $params[$param] = '%' . $term . '%';

        $columnParts = [];
        foreach ($columns as $column) {
            $columnParts[] = "$column LIKE $param";
        }
        $parts[] = '(' . implode(' OR ', $columnParts) . ')';
    }

    return $parts ? '(' . implode(' OR ', $parts) . ')' : '1=0';
}

function isAnyUserLoggedIn()
{
    return !empty($_SESSION['job_seeker_id']) || !empty($_SESSION['company_id']) || !empty($_SESSION['user_id']);
}

function getSmartSearchUrl($type, $id, $title)
{
    $loggedIn = isAnyUserLoggedIn();
    $role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';

    if (!$loggedIn) {
        return ['url' => 'user/login.php', 'button' => 'Login to View'];
    }

    if (!empty($_SESSION['job_seeker_id']) || $role === 'user') {
        if ($type === 'training') {
            return ['url' => 'user/trainings.php?search=' . urlencode($title) . '#training-' . (int)$id, 'button' => 'View Training'];
        }
        if ($type === 'task' || $type === 'challenge') {
            return ['url' => 'user/tasks.php?search=' . urlencode($title), 'button' => 'View Task'];
        }
        return ['url' => 'user/homepage.php', 'button' => 'Open Platform'];
    }

    if ($type === 'training') {
        return ['url' => 'company/training-details.php?id=' . (int)$id, 'button' => 'View Details'];
    }
    if ($type === 'task') {
        return ['url' => 'company/task-details.php?id=' . (int)$id, 'button' => 'View Details'];
    }

    return ['url' => 'company/employer-dashboard.php', 'button' => 'Open Dashboard'];
}

function normalizeSmartResult($type, $row)
{
    $title = $row['title'] ?? $row['company_name'] ?? 'Result';
    $link = getSmartSearchUrl($type, $row['id'] ?? 0, $title);

    return [
        'label' => ucfirst($type),
        'title' => $title,
        'subtitle' => $row['subtitle'] ?? $row['company_name'] ?? $row['category'] ?? $row['field'] ?? '',
        'description' => mb_substr((string)($row['description'] ?? $row['industry'] ?? $row['required_skills'] ?? ''), 0, 140) . (strlen((string)($row['description'] ?? '')) > 140 ? '...' : ''),
        'skills' => $row['required_skills'] ?? $row['category'] ?? $row['field'] ?? '',
        'url' => $link['url'],
        'button' => $link['button'],
    ];
}

function smartHomeSearch($conn, $query, $limit = 5)
{
    $terms = smartSearchTerms($query);
    $results = [
        'Trainings' => [],
        'Tasks' => [],
        'Challenges' => [],
        'Companies' => [],
    ];

    $params = [':limit' => (int)$limit];
    $where = buildSmartWhere(['t.title', 't.field', 't.required_skills', 't.requirements', 'c.company_name', 'c.industry'], $terms, $params);
    $rows = fetchRows($conn, "
        SELECT t.id, t.title, t.description, t.field, t.required_skills, c.company_name AS subtitle
        FROM trainings t
        LEFT JOIN companies c ON c.id = t.company_id
        WHERE t.status = 'published' AND t.deleted = 0 AND $where
        ORDER BY t.created_at DESC
        LIMIT :limit
    ", $params);
    foreach ($rows as $row) {
        $results['Trainings'][] = normalizeSmartResult('training', $row);
    }

    $params = [':limit' => (int)$limit];
    $where = buildSmartWhere(['t.title', 't.description', 't.category', 't.required_skills', 't.evaluation_criteria', 'c.company_name', 'c.industry'], $terms, $params);
    $rows = fetchRows($conn, "
        SELECT t.id, t.title, t.description, t.category, t.required_skills, c.company_name AS subtitle
        FROM tasks t
        LEFT JOIN companies c ON c.id = t.company_id
        WHERE t.status = 'published' AND t.deleted = 0 AND $where
        ORDER BY t.created_at DESC
        LIMIT :limit
    ", $params);
    foreach ($rows as $row) {
        $results['Tasks'][] = normalizeSmartResult('task', $row);
    }

    $params = [':limit' => (int)$limit];
    $where = buildSmartWhere(['title', 'description', 'category', 'required_skills', 'difficulty'], $terms, $params);
    $rows = fetchRows($conn, "
        SELECT id, title, description, category, required_skills
        FROM challenges
        WHERE status = 'published' AND $where
        ORDER BY created_at DESC
        LIMIT :limit
    ", $params);
    foreach ($rows as $row) {
        $results['Challenges'][] = normalizeSmartResult('challenge', $row);
    }

    $params = [':limit' => (int)$limit];
    $where = buildSmartWhere(['company_name', 'responsible_person', 'country', 'location', 'company_type', 'industry'], $terms, $params);
    $rows = fetchRows($conn, "
        SELECT id, company_name, industry, location, company_type
        FROM companies
        WHERE $where
        ORDER BY created_at DESC
        LIMIT :limit
    ", $params);
    foreach ($rows as $row) {
        $row['description'] = trim(($row['industry'] ?? '') . ' company located in ' . ($row['location'] ?? ''));
        $row['subtitle'] = $row['company_type'] ?? 'Company';
        $results['Companies'][] = normalizeSmartResult('company', $row);
    }

    return array_filter($results, fn($items) => !empty($items));
}



function flattenSmartHomeSearchResults($groupedResults, $maxItems = 6)
{
    $flat = [];
    foreach ($groupedResults as $groupTitle => $items) {
        foreach ($items as $item) {
            $item['group'] = $groupTitle;
            $flat[] = $item;
            if (count($flat) >= $maxItems) {
                return $flat;
            }
        }
    }
    return $flat;
}

function smartHomeSearchDropdownHtml($conn, $query, $limit = 6)
{
    $query = trim((string)$query);
    if ($query === '') {
        return '';
    }

    $groupedResults = smartHomeSearch($conn, $query, 3);
    $items = flattenSmartHomeSearchResults($groupedResults, $limit);

    ob_start();
    if (empty($items)) { ?>
        <div class="smart-no-result">
            No results for “<?= safe($query) ?>”
        </div>
    <?php } else { ?>
        <?php foreach ($items as $item): ?>
            <a href="<?= safe($item['url']) ?>" class="smart-drop-item">
                <span>
                    <small><?= safe($item['group']) ?></small>
                    <b><?= safe($item['title']) ?></b>
                </span>
                <em><?= safe($item['button']) ?></em>
            </a>
        <?php endforeach; ?>
    <?php }
    return trim(ob_get_clean());
}



function homeSessionRole()
{
    return $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
}

function homeCompanySideDashboardLink()
{
    $role = homeSessionRole();

    if (!empty($_SESSION['company_id'])) {
        if ($role === 'hr') {
            return 'hr/hr-dashboard.php';
        }
        if ($role === 'task_manager') {
            return 'task/task-dashboard.php';
        }
        if ($role === 'training_manager') {
            return 'training/training-dashboard.php';
        }
        if ($role === 'company') {
            return 'company/employer-dashboard.php';
        }
    }

    return '';
}

function homeUserSideDashboardLink()
{
    $role = homeSessionRole();

    if (!empty($_SESSION['job_seeker_id']) || $role === 'user') {
        return 'user/dashboard.php';
    }

    return '';
}

function homeStartHiringLink()
{
    $companyLink = homeCompanySideDashboardLink();
    return $companyLink !== '' ? $companyLink : 'company/login.php';
}

function homeFindJobLink()
{
    $userLink = homeUserSideDashboardLink();
    return $userLink !== '' ? $userLink : 'user/login.php';
}

?>
