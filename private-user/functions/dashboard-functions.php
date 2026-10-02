<?php

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function dashValue($value, $default = '-')
{
    return htmlspecialchars(($value !== null && $value !== '') ? $value : $default, ENT_QUOTES, 'UTF-8');
}

function fetchOne($conn, $sql, $params = [])
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchAllRows($conn, $sql, $params = [])
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isDashboardAjaxRequest()
{
    return (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (isset($_POST['ajax']) && $_POST['ajax'] === '1');
}

function dashboardJsonResponse($payload)
{
    if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
    exit;
}

function sectionFromUrl($url)
{
    $hash = parse_url($url, PHP_URL_FRAGMENT);
    return $hash ?: '';
}

function redirectTo($url)
{
    header("Location: " . $url);
    exit;
}

function dashboardRedirect($url, $success = true, $errors = [])
{
    if (isDashboardAjaxRequest()) {
        dashboardJsonResponse([
            'success' => $success,
            'redirect' => $url,
            'section' => sectionFromUrl($url),
            'errors' => $errors
        ]);
    }

    redirectTo($url);
}

function backWithDashboardErrors($errors, $old, $url)
{
    $_SESSION['dashboard_errors'] = $errors;
    $_SESSION['dashboard_old'] = $old;
    dashboardRedirect($url, false, $errors);
}

function oldOrDb($old, $db, $key, $default = '')
{
    if (isset($old[$key])) {
        return $old[$key];
    }

    return $db[$key] ?? $default;
}

function selectedValue($old, $db, $key, $value)
{
    return (string)oldOrDb($old, $db, $key) === (string)$value ? 'selected' : '';
}

function checkedValue($old, $db, $key)
{
    if (isset($old[$key])) {
        return !empty($old[$key]) ? 'checked' : '';
    }

    return !empty($db[$key] ?? null) ? 'checked' : '';
}

function dashboardStaticLists()
{
    $countriesFile = __DIR__ . "/../../public/countries.json";
    $jordanLocationsFile = __DIR__ . "/../../public/jordan_locations.json";

    $countries = file_exists($countriesFile) ? json_decode(file_get_contents($countriesFile), true) : [];
    $jordanLocations = file_exists($jordanLocationsFile) ? json_decode(file_get_contents($jordanLocationsFile), true) : [];

    if (!is_array($countries) || empty($countries)) {
        $countries = ["Jordan", "Palestine", "Saudi Arabia", "United Arab Emirates", "Qatar", "Kuwait", "Bahrain", "Oman", "Egypt", "Lebanon", "Syria", "Iraq", "Turkey", "United States", "United Kingdom", "Canada", "Germany", "France"];
    }

    if (!is_array($jordanLocations) || empty($jordanLocations)) {
        $jordanLocations = ["Amman", "Irbid", "Zarqa", "Balqa", "Madaba", "Karak", "Tafilah", "Ma'an", "Aqaba", "Jerash", "Ajloun", "Mafraq"];
    }

    return [
        'months' => ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        'skillLevels' => ["Beginner", "Intermediate", "Advanced", "Expert"],
        'skillYears' => ["Less than 1 year", "1 Year", "2 Years", "3 Years", "4+ Years"],
        'languageLevels' => ["Basic", "Intermediate", "Advanced", "Fluent", "Native"],
        'languageRatings' => ["Good", "Very Good", "Excellent"],
        'degrees' => ["University (Bachelor)", "Diploma", "Master", "PhD"],
        'countries' => $countries,
        'jordanLocations' => $jordanLocations
    ];
}

function initUserDashboard($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        redirectTo("login.php");
    }

    $user_id = (int)$_SESSION['job_seeker_id'];

    $errors = $_SESSION['dashboard_errors'] ?? [];
    $old = $_SESSION['dashboard_old'] ?? [];
    unset($_SESSION['dashboard_errors'], $_SESSION['dashboard_old']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handleDashboardPost($conn, $user_id);
        
    }

    return getDashboardPageData($conn, $user_id, $errors, $old);
}

function getDashboardPageData($conn, $user_id, $errors = [], $old = [])
{
    $lists = dashboardStaticLists();

    $user = fetchOne($conn, "SELECT * FROM job_seekers WHERE id = ?", [$user_id]);
    $profile = fetchOne($conn, "SELECT * FROM job_seeker_profiles WHERE job_seeker_id = ?", [$user_id]);

    if ($profile && $user && array_key_exists('about_me', $user)) {
        $profile['about_me'] = $user['about_me'];
    }

    $education = fetchOne($conn, "SELECT * FROM job_seeker_educations WHERE job_seeker_id = ?", [$user_id]);

    if ($user && empty($user['profile_image']) && !empty($profile['profile_image'])) {
        $user['profile_image'] = "uploads/profile_images/" . $profile['profile_image'];
    }

    $experiences = fetchAllRows($conn, "SELECT * FROM job_seeker_experiences WHERE job_seeker_id = ? ORDER BY id DESC", [$user_id]);
    $courses = fetchAllRows($conn, "SELECT * FROM job_seeker_courses WHERE job_seeker_id = ? ORDER BY id DESC", [$user_id]);
    $skills = fetchAllRows($conn, "SELECT * FROM job_seeker_skills WHERE job_seeker_id = ? ORDER BY id DESC", [$user_id]);
    $languages = fetchAllRows($conn, "SELECT * FROM job_seeker_languages WHERE job_seeker_id = ? ORDER BY id DESC", [$user_id]);
    $cvs = fetchAllRows($conn, "SELECT * FROM job_seeker_cvs WHERE job_seeker_id = ? ORDER BY id DESC", [$user_id]);
     $qrToken = ensureJobSeekerQrToken($conn, $user_id);
    $completed = 0;
    $total = 7;
    if ($user) $completed++;
    if ($profile) $completed++;
    if ($education) $completed++;
    if (!empty($experiences)) $completed++;
    if (!empty($courses)) $completed++;
    if (!empty($skills)) $completed++;
    if (!empty($languages)) $completed++;
     
    return array_merge($lists, [
    'user_id' => $user_id,
    'errors' => $errors,
    'old' => $old,
    'user' => $user,
    'profile' => $profile,
    'education' => $education,
    'experiences' => $experiences,
    'courses' => $courses,
    'skills' => $skills,
    'languages' => $languages,
    'cvs' => $cvs,
    'edit' => $_GET['edit'] ?? '',
    'editExp' => $_GET['edit_exp'] ?? '',
    'editCourse' => $_GET['edit_course'] ?? '',
    'editSkill' => $_GET['edit_skill'] ?? '',
    'editLang' => $_GET['edit_lang'] ?? '',
    'completion' => round(($completed / $total) * 100),
    'qrToken' => $qrToken,
    'qrCompletion' => getQrProfileCompletionStatus([
        'user' => $user,
        'profile' => $profile,
        'education' => $education,
        'experiences' => $experiences,
        'courses' => $courses,
        'skills' => $skills,
        'languages' => $languages
    ]),
]);
}

function handleDashboardPost($conn, $user_id)
{
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_hero':
            updateDashboardHero($conn, $user_id);
            break;
        case 'update_profile':
            updateDashboardProfile($conn, $user_id);
            break;
        case 'update_education':
            updateDashboardEducation($conn, $user_id);
            break;
        case 'save_experience':
            saveDashboardExperience($conn, $user_id);
            break;
        case 'delete_experience':
            deleteDashboardRow($conn, 'job_seeker_experiences', $user_id, 'experience');
            break;
        case 'save_course':
            saveDashboardCourse($conn, $user_id);
            break;
        case 'delete_course':
            deleteDashboardRow($conn, 'job_seeker_courses', $user_id, 'courses');
            break;
        case 'save_skill':
            saveDashboardSkill($conn, $user_id);
            break;
        case 'delete_skill':
            deleteDashboardRow($conn, 'job_seeker_skills', $user_id, 'skills');
            break;
        case 'save_language':
            saveDashboardLanguage($conn, $user_id);
            break;
        case 'delete_language':
            deleteDashboardRow($conn, 'job_seeker_languages', $user_id, 'languages');
            break;
                   case 'delete_language':
            deleteDashboardRow($conn, 'job_seeker_languages', $user_id, 'languages');
            break;
            case 'upload_cv':
    uploadDashboardCv($conn, $user_id);
    break;

        case 'change_password':
            changeDashboardPassword($conn, $user_id);
            break;
    }
} 
    


function updateDashboardHero($conn, $user_id)
{
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'job_title' => trim($_POST['job_title'] ?? ''),
        'mobile' => trim($_POST['mobile'] ?? '')
    ];

    $errors = validateDashboardHero($conn, $data, $user_id, $_FILES['profile_image'] ?? null);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit=hero#top");
    }

    $imagePath = uploadDashboardProfileImage($user_id, $_FILES['profile_image'] ?? null);

    if ($imagePath) {
        $stmt = $conn->prepare("UPDATE job_seekers SET full_name = ?, job_title = ?, mobile = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([$data['full_name'], $data['job_title'], $data['mobile'], $imagePath, $user_id]);
    } else {
        $stmt = $conn->prepare("UPDATE job_seekers SET full_name = ?, job_title = ?, mobile = ? WHERE id = ?");
        $stmt->execute([$data['full_name'], $data['job_title'], $data['mobile'], $user_id]);
    }

    dashboardRedirect("dashboard.php#top");
}

function uploadDashboardProfileImage($user_id, $file)
{
    if (!$file || empty($file['name'])) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $dir = __DIR__ . "/../../public/uploads/profile_images/";

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $fileName = "job_seeker_" . $user_id . "_" . time() . "." . $ext;

    if (move_uploaded_file($file['tmp_name'], $dir . $fileName)) {
        return "uploads/profile_images/" . $fileName;
    }

    return null;
}

function updateDashboardProfile($conn, $user_id)
{
    $data = [
        'birth_date' => $_POST['birth_date'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'nationality' => trim($_POST['nationality'] ?? ''),
        'about_me' => trim($_POST['about_me'] ?? ''),
        'residence_country' => trim($_POST['residence_country'] ?? ''),
        'location' => trim($_POST['location'] ?? ''),
        'job_status' => trim($_POST['job_status'] ?? ''),
        'profile_visibility' => trim($_POST['profile_visibility'] ?? ''),
        'years_experience' => trim($_POST['years_experience'] ?? ''),
        'minimum_salary' => trim($_POST['minimum_salary'] ?? ''),
        'currency' => trim($_POST['currency'] ?? 'JOD'),
        'salary_confidential' => isset($_POST['salary_confidential']) ? 1 : 0
    ];

    $errors = validateDashboardProfileFull($data);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit=profile#personal");
    }

    $salary = $data['salary_confidential'] ? null : $data['minimum_salary'];

    try {
        $conn->beginTransaction();

        /*
         * NOTE:
         * The database has `about_me` (Professional Summary) in `job_seekers`,
         * not in `job_seeker_profiles`.
         * Saving `about_me` inside job_seeker_profiles makes the query fail,
         * so nationality and the rest of the profile fields do not get saved.
         */
        $stmt = $conn->prepare("UPDATE job_seekers SET about_me = ? WHERE id = ?");
        $stmt->execute([$data['about_me'], $user_id]);

        $exists = fetchOne($conn, "SELECT id FROM job_seeker_profiles WHERE job_seeker_id = ?", [$user_id]);

        if ($exists) {
            $stmt = $conn->prepare("
                UPDATE job_seeker_profiles
                SET birth_date = ?,
                    gender = ?,
                    nationality = ?,
                    residence_country = ?,
                    location = ?,
                    job_status = ?,
                    profile_visibility = ?,
                    years_experience = ?,
                    minimum_salary = ?,
                    currency = ?,
                    salary_confidential = ?
                WHERE job_seeker_id = ?
            ");

            $stmt->execute([
                $data['birth_date'],
                $data['gender'],
                $data['nationality'],
                $data['residence_country'],
                $data['location'],
                $data['job_status'],
                $data['profile_visibility'],
                $data['years_experience'],
                $salary,
                $data['currency'],
                $data['salary_confidential'],
                $user_id
            ]);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO job_seeker_profiles
                (job_seeker_id, birth_date, gender, nationality, residence_country, location, job_status, profile_visibility, years_experience, minimum_salary, currency, salary_confidential)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $user_id,
                $data['birth_date'],
                $data['gender'],
                $data['nationality'],
                $data['residence_country'],
                $data['location'],
                $data['job_status'],
                $data['profile_visibility'],
                $data['years_experience'],
                $salary,
                $data['currency'],
                $data['salary_confidential']
            ]);
        }

        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        backWithDashboardErrors(
            ['profile' => 'Could not save profile information. Please try again.'],
            $data,
            "dashboard.php?edit=profile#personal"
        );
    }

    dashboardRedirect("dashboard.php#personal");
}
function updateDashboardEducation($conn, $user_id)
{
    $data = [
        'degree' => trim($_POST['degree'] ?? ''),
        'institution' => trim($_POST['institution'] ?? ''),
        'field' => trim($_POST['field'] ?? ''),
        'graduation_year' => trim($_POST['graduation_year'] ?? ''),
        'grade' => trim($_POST['grade'] ?? '')
    ];

    $errors = validateDashboardEducation($data);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit=education#education");
    }

    upsertByJobSeeker($conn, 'job_seeker_educations', $user_id, [
        'degree' => $data['degree'],
        'institution' => $data['institution'],
        'field' => $data['field'],
        'graduation_year' => $data['graduation_year'],
        'grade' => $data['grade']
    ]);

    dashboardRedirect("dashboard.php#education");
}

function saveDashboardExperience($conn, $user_id)
{
    $id = $_POST['id'] ?? '';
    $data = [
        'job_title' => trim($_POST['job_title'] ?? ''),
        'company_name' => trim($_POST['company_name'] ?? ''),
        'company_location' => trim($_POST['company_location'] ?? ''),
        'from_month' => trim($_POST['from_month'] ?? ''),
        'from_year' => trim($_POST['from_year'] ?? ''),
        'to_month' => trim($_POST['to_month'] ?? ''),
        'to_year' => trim($_POST['to_year'] ?? ''),
        'is_present' => isset($_POST['is_present']) ? 1 : 0,
        'description' => trim($_POST['description'] ?? '')
    ];

    $errors = validateDashboardExperience($data);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit_exp=" . ($id ?: 'new') . "#experience");
    }

    if ($data['is_present']) {
        $data['to_month'] = null;
        $data['to_year'] = null;
    }

    saveDashboardItem($conn, 'job_seeker_experiences', $user_id, $data, $id);
    dashboardRedirect("dashboard.php#experience");
}

function saveDashboardCourse($conn, $user_id)
{
    $id = $_POST['id'] ?? '';
    $data = [
        'course_title' => trim($_POST['course_title'] ?? ''),
        'course_provider' => trim($_POST['course_provider'] ?? ''),
        'course_month' => trim($_POST['course_month'] ?? ''),
        'course_year' => trim($_POST['course_year'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];

    $errors = validateCourse($data);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit_course=" . ($id ?: 'new') . "#courses");
    }

    saveDashboardItem($conn, 'job_seeker_courses', $user_id, $data, $id);
    dashboardRedirect("dashboard.php#courses");
}

function saveDashboardSkill($conn, $user_id)
{
    $id = $_POST['id'] ?? '';
    $data = [
        'skill_name' => trim($_POST['skill_name'] ?? ''),
        'skill_level' => trim($_POST['skill_level'] ?? ''),
        'years_experience' => trim($_POST['years_experience'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];

    $errors = validateSkill($data);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit_skill=" . ($id ?: 'new') . "#skills");
    }

    saveDashboardItem($conn, 'job_seeker_skills', $user_id, $data, $id);
    dashboardRedirect("dashboard.php#skills");
}

function saveDashboardLanguage($conn, $user_id)
{
    $id = $_POST['id'] ?? '';
    $data = [
        'language_name' => trim($_POST['language_name'] ?? ''),
        'proficiency_level' => trim($_POST['proficiency_level'] ?? ''),
        'reading_level' => trim($_POST['reading_level'] ?? ''),
        'writing_level' => trim($_POST['writing_level'] ?? '')
    ];

    $errors = validateLanguage($data);

    if (!empty($errors)) {
        backWithDashboardErrors($errors, $data, "dashboard.php?edit_lang=" . ($id ?: 'new') . "#languages");
    }

    saveDashboardItem($conn, 'job_seeker_languages', $user_id, $data, $id);
    dashboardRedirect("dashboard.php#languages");
}

function saveDashboardItem($conn, $table, $user_id, $data, $id = '')
{
    $columns = array_keys($data);

    if ($id) {
        $set = implode(', ', array_map(fn($c) => "$c = ?", $columns));
        $stmt = $conn->prepare("UPDATE $table SET $set WHERE id = ? AND job_seeker_id = ?");
        return $stmt->execute([...array_values($data), $id, $user_id]);
    }

    $columnSql = implode(', ', array_merge(['job_seeker_id'], $columns));
    $placeholders = implode(', ', array_fill(0, count($columns) + 1, '?'));
    $stmt = $conn->prepare("INSERT INTO $table ($columnSql) VALUES ($placeholders)");
    return $stmt->execute(array_merge([$user_id], array_values($data)));
}

function upsertByJobSeeker($conn, $table, $user_id, $data)
{
    $exists = fetchOne($conn, "SELECT id FROM $table WHERE job_seeker_id = ?", [$user_id]);
    $columns = array_keys($data);

    if ($exists) {
        $set = implode(', ', array_map(fn($c) => "$c = ?", $columns));
        $stmt = $conn->prepare("UPDATE $table SET $set WHERE job_seeker_id = ?");
        return $stmt->execute([...array_values($data), $user_id]);
    }

    $columnSql = implode(', ', array_merge(['job_seeker_id'], $columns));
    $placeholders = implode(', ', array_fill(0, count($columns) + 1, '?'));
    $stmt = $conn->prepare("INSERT INTO $table ($columnSql) VALUES ($placeholders)");
    return $stmt->execute(array_merge([$user_id], array_values($data)));
}

function deleteDashboardRow($conn, $table, $user_id, $section)
{
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ? AND job_seeker_id = ?");
    $stmt->execute([$id, $user_id]);
    dashboardRedirect("dashboard.php#$section");
}

function validateDashboardHero($conn, $data, $user_id, $file = null)
{
    $errors = [];

    if ($data['full_name'] === '') {
        $errors['full_name'] = "Full name is required";
    } elseif (!preg_match("/^[A-Za-z\s'-]{2,80}$/", $data['full_name'])) {
        $errors['full_name'] = "Full name must contain letters only";
    }

    if ($data['job_title'] === '') {
        $errors['job_title'] = "Job title is required";
    } elseif (!preg_match("/^[A-Za-z\s&.,()'-]{2,80}$/", $data['job_title'])) {
        $errors['job_title'] = "Job title contains invalid characters";
    }

    if (!preg_match("/^07[789][0-9]{7}$/", $data['mobile'])) {
        $errors['mobile'] = "Enter a valid Jordanian number (07XXXXXXXX)";
    } else {
        $check = $conn->prepare("SELECT COUNT(*) FROM job_seekers WHERE mobile = ? AND id != ?");
        $check->execute([$data['mobile'], $user_id]);

        if ((int)$check->fetchColumn() > 0) {
            $errors['mobile'] = "This mobile number is already used";
        }
    }

    if ($file && !empty($file['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors['profile_image'] = "Image must be JPG, PNG, or WEBP";
        }

        if (!empty($file['size']) && $file['size'] > 5 * 1024 * 1024) {
            $errors['profile_image'] = "Image size must be less than 5MB";
        }
    }

    return $errors;
}

function validateDashboardProfileFull($data)
{
    $errors = [];
    $lists = dashboardStaticLists();

    if (empty($data['birth_date'])) {
        $errors['birth_date'] = "Birth date is required";
    } else {
        $age = date_diff(date_create($data['birth_date']), date_create('today'))->y;
        if ($age < 16) {
            $errors['birth_date'] = "You must be at least 16 years old";
        }
    }

    if (!in_array($data['gender'], ['male', 'female'])) {
        $errors['gender'] = "Select gender";
    }

    if (!in_array($data['nationality'], $lists['countries'])) {
        $errors['nationality'] = "Select a valid nationality";
    }

    if (!in_array($data['residence_country'], $lists['countries'])) {
        $errors['residence_country'] = "Select a valid residence country";
    }

    if ($data['location'] === '') {
        $errors['location'] = "Location is required";
    } elseif ($data['residence_country'] === 'Jordan' && !in_array($data['location'], $lists['jordanLocations'])) {
        $errors['location'] = "Select a valid Jordan location";
    } elseif ($data['residence_country'] !== 'Jordan' && !preg_match("/^[A-Za-z\s\-'.,]+$/", $data['location'])) {
        $errors['location'] = "Location contains invalid characters";
    }

    if (!in_array($data['job_status'], ['unemployed', 'working_open', 'not_looking'])) {
        $errors['job_status'] = "Select job status";
    }

    if (!in_array($data['profile_visibility'], ['public', 'registered', 'hidden'])) {
        $errors['profile_visibility'] = "Select profile visibility";
    }

    if ($data['years_experience'] === '' || !is_numeric($data['years_experience']) || $data['years_experience'] < 0 || $data['years_experience'] > 60) {
        $errors['years_experience'] = "Enter valid years of experience";
    }

    if (empty($data['salary_confidential'])) {
        if ($data['minimum_salary'] === '' || !is_numeric($data['minimum_salary']) || $data['minimum_salary'] < 0) {
            $errors['minimum_salary'] = "Enter a valid salary";
        }
    }

    return $errors;
}

function validateDashboardEducation($data)
{
    $errors = [];

    foreach (['degree' => 'Degree', 'institution' => 'Institution', 'field' => 'Field', 'graduation_year' => 'Graduation year', 'grade' => 'Grade'] as $key => $label) {
        if (trim($data[$key] ?? '') === '') {
            $errors[$key] = "$label is required";
        }
    }

    return $errors;
}

function validateDashboardExperience($data)
{
    $errors = [];

    if ($data['job_title'] === '') $errors['job_title'] = "Job title is required";
    if ($data['company_name'] === '') $errors['company_name'] = "Company name is required";
    if ($data['from_month'] === '' || $data['from_year'] === '') $errors['from_date'] = "From date is required";

    if (!$data['is_present'] && ($data['to_month'] === '' || $data['to_year'] === '')) {
        $errors['to_date'] = "To date is required or choose To Present";
    }

    $monthNums = ["January" => 1, "February" => 2, "March" => 3, "April" => 4, "May" => 5, "June" => 6, "July" => 7, "August" => 8, "September" => 9, "October" => 10, "November" => 11, "December" => 12];

    if (!$data['is_present'] && !empty($data['from_year']) && !empty($data['to_year']) && !empty($data['from_month']) && !empty($data['to_month'])) {
        if ((int)$data['to_year'] < (int)$data['from_year'] || ((int)$data['to_year'] === (int)$data['from_year'] && $monthNums[$data['to_month']] < $monthNums[$data['from_month']])) {
            $errors['date'] = "End date must be after start date";
        }
    }

    return $errors;
}

function validateCourse($data)
{
    $errors = [];
    if ($data['course_title'] === '') $errors['course_title'] = "Course title is required";
    if ($data['course_provider'] === '') $errors['course_provider'] = "Course provider is required";
    if ($data['course_month'] === '' || $data['course_year'] === '') $errors['course_date'] = "Course date is required";
    return $errors;
}

function validateSkill($data)
{
    $errors = [];
    if ($data['skill_name'] === '') $errors['skill_name'] = "Skill name is required";
    if ($data['skill_level'] === '') $errors['skill_level'] = "Skill level is required";
    return $errors;
}

function validateLanguage($data)
{
    $errors = [];
    if ($data['language_name'] === '') $errors['language_name'] = "Language name is required";
    if ($data['proficiency_level'] === '') $errors['proficiency_level'] = "Proficiency level is required";
    return $errors;
}
function changeDashboardPassword($conn, $user_id)
{
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $errors = [];

    $stmt = $conn->prepare("
        SELECT password
        FROM job_seekers
        WHERE id = ?
    ");

    $stmt->execute([$user_id]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password'])) {
        $errors['current_password'] = "Current password is incorrect";
    }

    if (strlen($new) < 8) {
        $errors['new_password'] = "Password must be at least 8 characters";
    }

    if ($new !== $confirm) {
        $errors['confirm_password'] = "Passwords do not match";
    }

    if (!empty($errors)) {
        backWithDashboardErrors(
            $errors,
            [],
            "dashboard.php#password"
        );
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE job_seekers
        SET password = ?
        WHERE id = ?
    ");

    $stmt->execute([$hashed, $user_id]);

    dashboardRedirect("dashboard.php#password");
}
function generateQrToken()
{
    return bin2hex(random_bytes(24));
}

function ensureJobSeekerQrToken(PDO $conn, int $user_id)
{
    $stmt = $conn->prepare("
        SELECT qr_token
        FROM job_seekers
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $token = $stmt->fetchColumn();

    if (!empty($token)) {
        return $token;
    }

    $token = generateQrToken();

    $stmt = $conn->prepare("
        UPDATE job_seekers
        SET qr_token = ?
        WHERE id = ?
    ");
    $stmt->execute([$token, $user_id]);

    return $token;
}

function getQrProfileCompletionStatus(array $page)
{
    $missing = [];

    if (empty($page['user']['full_name'])) {
        $missing[] = 'Full name';
    }

    if (empty($page['user']['job_title'])) {
        $missing[] = 'Job title';
    }

    if (empty($page['profile'])) {
        $missing[] = 'Personal information';
    }

    if (empty($page['education'])) {
        $missing[] = 'Education';
    }

    if (empty($page['skills']) || count($page['skills']) < 3) {
        $missing[] = 'At least 3 skills';
    }

    if (empty($page['languages'])) {
        $missing[] = 'At least one language';
    }

    if (empty($page['experiences']) && empty($page['courses'])) {
        $missing[] = 'Experience or course';
    }

    return [
        'is_complete' => empty($missing),
        'missing' => $missing,
        'percentage' => empty($missing) ? 100 : max(0, 100 - (count($missing) * 15))
    ];
}
function uploadDashboardCv($conn, $user_id)
{
    if (
        empty($_FILES['cv_file']) ||
        empty($_FILES['cv_file']['name'])
    ) {
        dashboardRedirect("dashboard.php");
    }

    $file = $_FILES['cv_file'];

    $allowed = ['pdf', 'doc', 'docx'];

    $ext = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($ext, $allowed)) {

        backWithDashboardErrors(
            ['cv' => 'Only PDF, DOC, DOCX allowed'],
            [],
            "dashboard.php"
        );
    }

    $uploadDir =
        __DIR__ .
        "/../../public/uploads/cvs/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName =
        time() .
        "_" .
        $user_id .
        "_" .
        preg_replace(
            "/[^A-Za-z0-9_\-.]/",
            "_",
            $file['name']
        );

    $targetPath =
        $uploadDir .
        $newFileName;

    if (
        move_uploaded_file(
            $file['tmp_name'],
            $targetPath
        )
    ) {

        $stmt = $conn->prepare("
            INSERT INTO job_seeker_cvs
            (job_seeker_id, cv_file)
            VALUES (?, ?)
        ");

        $stmt->execute([
            $user_id,
            $newFileName
        ]);
    }

    dashboardRedirect("dashboard.php");
}