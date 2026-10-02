<?php

if (!function_exists('getJobSeekerBasic')) {
   function getJobSeekerBasic($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT 
            js.full_name,
            js.job_title,
            js.profile_image,
            jsp.location,
            jsp.residence_country
        FROM job_seekers js
        LEFT JOIN job_seeker_profiles jsp 
            ON jsp.job_seeker_id = js.id
        WHERE js.id = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}

function initCoursesHomePage($conn)
{
    if (!isset($_SESSION['job_seeker_id'])) {
        header("Location: ../../public/user/login.php");
        exit;
    }

    $user_id = (int)$_SESSION['job_seeker_id'];
    $user = getJobSeekerBasic($conn, $user_id);

    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? 'All');

    if (isset($_POST['comment_post_id'])) {
        addLearningComment(
            $conn,
            (int)$_POST['comment_post_id'],
            $user_id,
            $_POST['comment_text'] ?? ''
        );

        header("Location: courseshome.php#post-" . (int)$_POST['comment_post_id']);
        exit;
    }

    $postResult = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['comment_post_id'])) {
        $postResult = createLearningPost(
            $conn,
            $user_id,
            $_POST,
            $_FILES['thumbnail'] ?? []
        );

        if (!empty($postResult['success'])) {
            header("Location: courseshome.php?posted=1");
            exit;
        }
    }

    $feed = getLearningFeed($conn, $search, $category);
    $learningCategories = getLearningCategories($conn);
    $conversations = getLearningConversations($conn, $user_id);
    $unreadMessages = countUnreadLearningMessages($conn, $user_id);

    $userName = $user['full_name'] ?? 'Tamkeen User';
    $userTitle = $user['job_title'] ?? 'Skill Exchange Member';

    $totalPosts = count($feed);
    $offerCount = count(array_filter($feed, fn($item) => ($item['type'] ?? 'post') === 'offer'));
    $requestCount = count(array_filter($feed, fn($item) => ($item['type'] ?? 'post') === 'request'));
    $groupCount = count(array_filter($feed, fn($item) => ($item['type'] ?? 'post') === 'group'));

    return [
        'user_id' => $user_id,
        'user' => $user,
        'search' => $search,
        'category' => $category,
        'postResult' => $postResult,
        'feed' => $feed,
        'learningCategories' => $learningCategories,
        'conversations' => $conversations,
        'unreadMessages' => $unreadMessages,
        'userName' => $userName,
        'userTitle' => $userTitle,
        'totalPosts' => $totalPosts,
        'offerCount' => $offerCount,
        'requestCount' => $requestCount,
        'groupCount' => $groupCount
    ];
}

function createLearningPost($conn, $user_id, $data, $file)
{
    $errors = [];

    $type = trim($data['type'] ?? '');
    $title = trim($data['title'] ?? '');
    $content = trim($data['content'] ?? '');
    $category = trim($data['category'] ?? '');
    $duration = trim($data['duration'] ?? '');

    if ($type === '') {
        $errors['type'] = "Post type is required";
    }

    if ($title === '') {
        $errors['title'] = "Title is required";
    }

    if ($content === '') {
        $errors['content'] = "Description is required";
    }

    if ($category === '') {
        $errors['category'] = "Category is required";
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $thumbnail = null;

    if (!empty($file['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return [
                'success' => false,
                'errors' => ['thumbnail' => 'Invalid image type']
            ];
        }

        $dir = __DIR__ . "/../../public/uploads/learning/";

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = "exchange_" . (int)$user_id . "_" . time() . "." . $ext;

        if (move_uploaded_file($file['tmp_name'], $dir . $fileName)) {
            $thumbnail = "uploads/learning/" . $fileName;
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO learning_posts
        (user_id, type, title, content, category, thumbnail, duration)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $success = $stmt->execute([
        $user_id,
        $type,
        $title,
        $content,
        $category,
        $thumbnail,
        $duration
    ]);

    return [
        'success' => $success,
        'errors' => []
    ];
}

function getLearningFeed($conn, $search = '', $category = 'All')
{
    $where = ["1=1"];
    $params = [];

    if ($search !== '') {
        $where[] = "(
            lp.title LIKE ?
            OR lp.content LIKE ?
            OR lp.category LIKE ?
            OR js.full_name LIKE ?
        )";

        $s = "%" . $search . "%";
        array_push($params, $s, $s, $s, $s);
    }

    if ($category !== 'All') {
        $where[] = "lp.category = ?";
        $params[] = $category;
    }

    $stmt = $conn->prepare("
        SELECT
            lp.*,
            js.full_name,
            js.job_title,
            js.profile_image
        FROM learning_posts lp
        JOIN job_seekers js ON js.id = lp.user_id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY lp.created_at DESC
    ");

    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLearningCategories($conn)
{
    $stmt = $conn->prepare("
        SELECT category, COUNT(*) total
        FROM learning_posts
        WHERE category IS NOT NULL
        AND category != ''
        GROUP BY category
        ORDER BY total DESC
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addLearningComment($conn, $post_id, $user_id, $comment)
{
    $comment = trim($comment);

    if ($comment === '') {
        return false;
    }

    $stmt = $conn->prepare("
        INSERT INTO learning_comments
        (post_id, user_id, comment)
        VALUES (?, ?, ?)
    ");

    return $stmt->execute([
        $post_id,
        $user_id,
        $comment
    ]);
}

function getLearningComments($conn, $post_id)
{
    $stmt = $conn->prepare("
        SELECT
            lc.*,
            js.full_name,
            js.profile_image
        FROM learning_comments lc
        JOIN job_seekers js ON js.id = lc.user_id
        WHERE lc.post_id = ?
        ORDER BY lc.created_at DESC
    ");

    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function joinLearningPost($conn, $post_id, $user_id)
{
    $stmt = $conn->prepare("
        INSERT IGNORE INTO learning_joins
        (post_id, user_id)
        VALUES (?, ?)
    ");

    return $stmt->execute([
        $post_id,
        $user_id
    ]);
}

function countLearningJoins($conn, $post_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM learning_joins
        WHERE post_id = ?
    ");

    $stmt->execute([$post_id]);
    return (int)$stmt->fetchColumn();
}

function sendLearningMessage($conn, $post_id, $sender_id, $receiver_id, $message)
{
    $message = trim($message);

    if ($message === '' || (int)$sender_id === (int)$receiver_id) {
        return false;
    }

    $stmt = $conn->prepare("
        INSERT INTO learning_messages
        (post_id, sender_id, receiver_id, message)
        VALUES (?, ?, ?, ?)
    ");

    return $stmt->execute([
        $post_id,
        $sender_id,
        $receiver_id,
        $message
    ]);
}

function getLearningConversations($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT
            lm.*,
            CASE
                WHEN lm.sender_id = ? THEN lm.receiver_id
                ELSE lm.sender_id
            END AS other_user_id,
            js.full_name,
            js.profile_image
        FROM learning_messages lm
        JOIN job_seekers js
            ON js.id = CASE
                WHEN lm.sender_id = ? THEN lm.receiver_id
                ELSE lm.sender_id
            END
        INNER JOIN (
            SELECT
                CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END AS other_id,
                MAX(id) AS last_message_id
            FROM learning_messages
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY other_id
        ) last_msg ON last_msg.last_message_id = lm.id
        WHERE lm.sender_id = ? OR lm.receiver_id = ?
        ORDER BY lm.created_at DESC
    ");

    $stmt->execute([
        $user_id,
        $user_id,
        $user_id,
        $user_id,
        $user_id,
        $user_id,
        $user_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLearningChatMessages($conn, $user_id, $other_user_id)
{
    $stmt = $conn->prepare("
        SELECT
            lm.*,
            sender.full_name AS sender_name
        FROM learning_messages lm
        JOIN job_seekers sender ON sender.id = lm.sender_id
        WHERE
            (lm.sender_id = ? AND lm.receiver_id = ?)
            OR
            (lm.sender_id = ? AND lm.receiver_id = ?)
        ORDER BY lm.created_at ASC
    ");

    $stmt->execute([
        $user_id,
        $other_user_id,
        $other_user_id,
        $user_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markLearningMessagesAsRead($conn, $user_id, $other_user_id)
{
    $stmt = $conn->prepare("
        UPDATE learning_messages
        SET is_read = 1
        WHERE receiver_id = ?
        AND sender_id = ?
        AND is_read = 0
    ");

    return $stmt->execute([
        $user_id,
        $other_user_id
    ]);
}

function countUnreadLearningMessages($conn, $user_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM learning_messages
        WHERE receiver_id = ?
        AND is_read = 0
    ");

    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}
