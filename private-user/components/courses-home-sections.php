<?php

function renderCoursesHomePage($page, $conn){
    extract($page);
    $base_url = $base_url ?? '../../';
?>


<main class="container py-4">

   <?php if (isset($_GET['posted'])): ?>
    <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
        Your skill exchange post was published successfully.
    </div>
<?php endif; ?>

    <!-- Mini Stats -->
    <section class="learning-mini-strip mb-4">
        <div class="mini-learning-card">
            <small>Skill Offers</small>
            <strong><?= $offerCount ?></strong>
        </div>

        <div class="mini-learning-card">
            <small>Learning Requests</small>
            <strong><?= $requestCount ?></strong>
        </div>

        <div class="mini-learning-card">
            <small>Study Groups</small>
            <strong><?= $groupCount ?></strong>
        </div>

        <div class="mini-learning-card">
            <small>Community Posts</small>
            <strong><?= $totalPosts ?></strong>
        </div>
    </section>

    <!-- Search + Filters -->
    <section class="mb-4">
<form method="GET" class="row g-3 align-items-center position-relative">
                <div class="col-lg-5">
                <div class="course-search d-flex align-items-center position-relative">
    <i class="fa fa-search text-primary ms-4"></i>

    <input
        type="text"
        id="courseSearch"
        name="search"
        value="<?= htmlspecialchars($search) ?>"
        placeholder="Search skills, offers, requests, groups..."
        autocomplete="off">

    <div id="courseSearchSuggestions" class="search-suggestions d-none"></div>
</div>
            </div>

            <div class="col-lg-7">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="courseshome.php" class="course-filter <?= $category === 'All' ? 'active' : '' ?>">All</a>

                    <?php foreach ($learningCategories as $cat): ?>
                        <a href="courseshome.php?category=<?= urlencode($cat['category']) ?>&search=<?= urlencode($search) ?>"
                           class="course-filter <?= $category === $cat['category'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['category']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </section>

    <div class="row g-4">

        <!-- Left Sidebar -->
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="side-sticky">

                <div class="card course-card mb-4">
                    <div class="card-body text-center p-4">
                        <?php if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])): ?>
                            <img src="../<?= htmlspecialchars($user['profile_image']) ?>"
                                 class="rounded-circle shadow-sm mb-3"
                                 style="width:90px;height:90px;object-fit:cover;">
                        <?php else: ?>
                            <div class="profile-avatar-fallback mx-auto mb-3">
                                <?= strtoupper(substr($userName, 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($userName) ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($userTitle) ?></p>

                        <a href="public-profile.php" class="btn btn-outline-primary rounded-pill w-100 fw-bold">
                            View Profile
                        </a>
                    </div>
                </div>

                

                <div class="card course-card" id="groups">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Study Groups</h5>

                        <?php
                        $groups = array_filter($feed, fn($item) => ($item['type'] ?? 'post') === 'group');
                        $groups = array_slice($groups, 0, 3);
                        ?>

                        <?php if (!empty($groups)): ?>
                            <?php foreach ($groups as $group): ?>
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($group['title'] ?: 'Study Group') ?></h6>
                                    <small class="text-muted d-block mb-2"><?= htmlspecialchars($group['category'] ?: 'Skills') ?></small>
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm rounded-pill open-chat-btn"
                                            data-receiver-id="<?= (int)$group['user_id'] ?>"
                                            data-post-id="<?= (int)$group['id'] ?>"
                                            data-name="<?= htmlspecialchars($group['full_name']) ?>">
                                        Contact Owner
                                    </button>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small mb-3">No groups yet. Start the first skill group.</p>
                            <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#learningPostModal">
                                Create Group
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </aside>

        <!-- Center Feed -->
        <section class="col-lg-6">

            <!-- Create Exchange Post -->
            <div class="card course-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <?php if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])): ?>
                            <img src="../<?= htmlspecialchars($user['profile_image']) ?>" class="course-avatar" alt="">
                        <?php else: ?>
                            <div class="course-avatar-fallback">
                                <?= strtoupper(substr($userName, 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <button class="btn btn-light rounded-pill text-start flex-grow-1 px-4 py-3 text-muted"
                                data-bs-toggle="modal"
                                data-bs-target="#learningPostModal">
                            Offer a skill, request a skill, or create a study group...
                        </button>
                    </div>

                    <div class="d-flex justify-content-around border-top pt-3">
                        <button class="btn btn-white fw-bold text-muted" data-bs-toggle="modal" data-bs-target="#learningPostModal">
                            <i class="fa fa-hand-holding-heart text-primary me-2"></i> Offer Skill
                        </button>

                        <button class="btn btn-white fw-bold text-muted" data-bs-toggle="modal" data-bs-target="#learningPostModal">
                            <i class="fa fa-search text-primary me-2"></i> Request Skill
                        </button>

                        <button class="btn btn-white fw-bold text-muted" data-bs-toggle="modal" data-bs-target="#learningPostModal">
                            <i class="fa fa-users text-primary me-2"></i> Group
                        </button>
                    </div>
                </div>
            </div>

            <?php if (empty($feed)): ?>
                <div class="card course-card mb-4">
                    <div class="card-body p-5 text-center">
                        <i class="fa fa-exchange-alt fa-3x text-primary mb-3"></i>
                        <h4 class="fw-bold">No skill exchanges yet</h4>
                        <p class="text-muted mb-0">Create the first offer, request, or study group.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($feed as $item): ?>
                <?php
                $type = $item['type'] ?? 'post';
                $postComments = getLearningComments($conn, $item['id']);

                $typeLabel = match($type) {
                    'offer' => 'Skill Offer',
                    'request' => 'Skill Request',
                    'group' => 'Study Group',
                    default => 'Community Post'
                };

                $badgeClass = match($type) {
                    'offer' => 'bg-primary',
                    'request' => 'bg-warning text-dark',
                    'group' => 'bg-success',
                    default => 'bg-secondary'
                };
                ?>

                <div class="card course-card mb-4 exchange-post-item"
     id="post-<?= (int)$item['id'] ?>"
     data-title="<?= strtolower(htmlspecialchars($item['title'] ?? '')) ?>"
     data-name="<?= strtolower(htmlspecialchars($item['full_name'] ?? '')) ?>"
     data-category="<?= strtolower(htmlspecialchars($item['category'] ?? '')) ?>"
     data-content="<?= strtolower(htmlspecialchars($item['content'] ?? '')) ?>">
                    <div class="card-body p-4">

                        <div class="d-flex gap-3 mb-3">
                            <?php if (!empty($item['profile_image']) && file_exists('../' . $item['profile_image'])): ?>
                                <img src="../<?= htmlspecialchars($item['profile_image']) ?>" class="course-avatar" alt="">
                            <?php else: ?>
                                <div class="course-avatar-fallback">
                                    <?= strtoupper(substr($item['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($item['full_name']) ?></h6>
                                <small class="text-muted">
                                    <?= $typeLabel ?> · <?= date('M d, Y', strtotime($item['created_at'])) ?>
                                </small>
                            </div>
                        </div>

                        <span class="badge <?= $badgeClass ?> rounded-pill mb-3">
                            <?= $typeLabel ?>
                        </span>

                        <?php if (!empty($item['title'])): ?>
                            <h4 class="fw-bold mb-2"><?= htmlspecialchars($item['title']) ?></h4>
                        <?php endif; ?>

                        <p class="text-muted">
                            <?= nl2br(htmlspecialchars($item['content'])) ?>
                        </p>

                        <?php if (!empty($item['thumbnail'])): ?>
                            <img src="../<?= htmlspecialchars($item['thumbnail']) ?>"
                                 class="img-fluid course-img w-100 mb-3"
                                 alt="">
                        <?php endif; ?>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php if (!empty($item['category'])): ?>
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    <?= htmlspecialchars($item['category']) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($item['duration'])): ?>
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    <?= htmlspecialchars($item['duration']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3 flex-wrap gap-2">

                            <button type="button"
                                    class="btn btn-white fw-bold text-muted"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#comments-<?= (int)$item['id'] ?>">
                                💬 Comment
                            </button>

                            

                            <button type="button"
                                    class="btn btn-white fw-bold text-muted open-chat-btn"
                                    data-receiver-id="<?= (int)$item['user_id'] ?>"
                                    data-post-id="<?= (int)$item['id'] ?>"
                                    data-name="<?= htmlspecialchars($item['full_name']) ?>">
                                🤝 Connect
                            </button>

                        </div>

                        <div class="collapse mt-3" id="comments-<?= (int)$item['id'] ?>">
                            <div class="border-top pt-3">

                                <form method="POST" class="mb-4">
                                    <input type="hidden" name="comment_post_id" value="<?= (int)$item['id'] ?>">

                                    <div class="d-flex gap-2">
                                        <textarea
                                            name="comment_text"
                                            class="form-control rounded-pill"
                                            rows="1"
                                            placeholder="Write a comment..."
                                            required></textarea>

                                        <button class="btn btn-primary rounded-pill px-4">
                                            Post
                                        </button>
                                    </div>
                                </form>

                                <?php if (!empty($postComments)): ?>
                                    <?php foreach ($postComments as $comment): ?>
                                        <?php
                                        $commentHasImage = !empty($comment['profile_image']) && file_exists('../' . $comment['profile_image']);
                                        ?>

                                        <div class="d-flex gap-2 mb-3">
                                            <?php if ($commentHasImage): ?>
                                                <img src="../<?= htmlspecialchars($comment['profile_image']) ?>" class="course-avatar" alt="">
                                            <?php else: ?>
                                                <div class="course-avatar-fallback">
                                                    <?= strtoupper(substr($comment['full_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="bg-light rounded-4 px-3 py-2 flex-grow-1">
                                                <h6 class="fw-bold mb-1">
                                                    <?= htmlspecialchars($comment['full_name']) ?>
                                                </h6>

                                                <p class="mb-1 small">
                                                    <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                                                </p>

                                                <small class="text-muted">
                                                    <?= date('M d · h:i A', strtotime($comment['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted small mb-0">No comments yet.</p>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>

        </section>

        <!-- Right Sidebar -->
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="side-sticky">

                <div class="card course-card mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">How It Works</h5>

                        <div class="bg-light rounded-4 p-3">
                            <ol class="small text-muted mb-0 ps-3">
                                <li>Post a skill you can teach</li>
                                <li>Post a skill you want to learn</li>
                                <li>Find a match</li>
                                <li>Message and exchange knowledge</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="card course-card mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Popular Exchanges</h5>

                        <?php foreach (array_slice($learningCategories, 0, 5) as $cat): ?>
                            <a href="courseshome.php?category=<?= urlencode($cat['category']) ?>"
                               class="d-flex justify-content-between text-dark mb-3 text-decoration-none">
                                <span><?= htmlspecialchars($cat['category']) ?></span>
                                <strong><?= (int)$cat['total'] ?></strong>
                            </a>
                        <?php endforeach; ?>

                        <?php if (empty($learningCategories)): ?>
                            <p class="text-muted small mb-0">No exchanges yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card course-card">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Active Members</h5>

                        <?php
                        $seen = [];
                        $activeLearners = [];
                        foreach ($feed as $item) {
                            if (!in_array($item['user_id'], $seen)) {
                                $seen[] = $item['user_id'];
                                $activeLearners[] = $item;
                            }
                        }
                        $activeLearners = array_slice($activeLearners, 0, 3);
                        ?>

                        <?php foreach ($activeLearners as $member): ?>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <?php if (!empty($member['profile_image']) && file_exists('../' . $member['profile_image'])): ?>
                                    <img src="../<?= htmlspecialchars($member['profile_image']) ?>" class="course-avatar" alt="">
                                <?php else: ?>
                                    <div class="course-avatar-fallback">
                                        <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <h6 class="fw-bold mb-0"><?= htmlspecialchars($member['full_name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($member['category'] ?: 'Skill Exchange') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($activeLearners)): ?>
                            <p class="text-muted small mb-0">No active members yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </aside>

    </div>
</main>

<!-- Messaging Box -->
<div class="messaging-box collapsed" id="learningMessenger">

    <div class="messaging-header" id="messengerToggle">
        <div class="d-flex align-items-center gap-2">
            <strong id="messengerTitle">Messaging</strong>

            <?php if ($unreadMessages > 0): ?>
                <span class="message-badge">
                    <?= $unreadMessages ?>
                </span>
            <?php endif; ?>
        </div>

        <div>
            <i class="fa fa-chevron-up" id="messengerIcon"></i>
        </div>
    </div>

    <div class="messenger-body">

        <div class="conversation-list" id="conversationList">
            <?php if (empty($conversations)): ?>
                <div class="message-item text-muted small">
                    No conversations yet.
                </div>
            <?php endif; ?>

            <?php foreach ($conversations as $conv): ?>
                <div class="message-item conversation-item"
                     data-user-id="<?= (int)$conv['other_user_id'] ?>"
                     data-name="<?= htmlspecialchars($conv['full_name']) ?>">

                    <strong><?= htmlspecialchars($conv['full_name']) ?></strong>
                    <p class="small text-muted mb-0">
                        <?= htmlspecialchars(substr($conv['message'], 0, 55)) ?>...
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="chat-panel d-none" id="chatPanel">
            <div class="chat-header">
                <button type="button" id="backToConversations">
                    <i class="fa fa-arrow-left"></i>
                </button>
                <strong id="chatWithName">Chat</strong>
            </div>

            <div class="chat-messages" id="chatMessages"></div>

            <form id="chatForm" class="chat-form">
                <input type="hidden" id="chatReceiverId">
                <input type="hidden" id="chatPostId" value="0">

                <input type="text"
                       id="chatMessageInput"
                       placeholder="Write a message..."
                       autocomplete="off">

                <button type="submit">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </form>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="learningPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">

            <form method="POST" enctype="multipart/form-data" id="exchangePostForm">

                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Create Skill Exchange Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <select name="type" class="form-select rounded-pill mb-3" id="learningTypeSelect" required>
    <option value="" selected disabled>Choose post type</option>
    <option value="offer">I Can Teach</option>
    <option value="request">I Want To Learn</option>
    <option value="group">Study Group</option>
    <option value="post">Community Post</option>
</select>
<small class="text-danger d-none mb-3" id="typeError">
    Post type is required
</small>

                    <input type="text"
                           name="title"
                           id="exchangeTitle"
                           class="form-control rounded-pill mb-2"
                           placeholder="Example: I can teach Power BI / I want to learn Excel">

                    <small class="text-danger d-none mb-3" id="titleError">
                        Title is required
                    </small>

                    <textarea name="content"
                              id="exchangeContent"
                              class="form-control border-0 bg-light rounded-4 mb-2"
                              rows="5"
                              placeholder="Explain what you can teach, what you want to learn, or who you want to connect with..."></textarea>

                    <small class="text-danger d-none mb-3" id="contentError">
                        Description is required
                    </small>

                    <input type="text"
                           name="category"
                           id="exchangeCategory"
                           class="form-control rounded-pill mb-2"
                           placeholder="Skill category: Excel, Power BI, English, Design...">

                    <small class="text-danger d-none mb-3" id="categoryError">
                        Category is required
                    </small>

                    <input type="text"
                           name="duration"
                           class="form-control rounded-pill mb-3"
                           placeholder="Availability, example: Weekends / 2 hours weekly">

                    <input type="file"
                           name="thumbnail"
                           class="form-control rounded-pill mb-3"
                           accept="image/*">

                </div>

                <div class="modal-footer border-0">
                    <button class="btn btn-primary rounded-pill w-100" id="exchangeSubmitBtn">
                        Publish Exchange
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    window.currentUserId = <?= (int)$user_id ?>;
</script>
<script src="<?= htmlspecialchars($base_url . 'js/skill-exchange.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("courseSearch");
    const suggestions = document.getElementById("courseSearchSuggestions");
    const form = input ? input.closest("form") : null;
    const items = document.querySelectorAll(".exchange-post-item");

    if (!input || !suggestions || !form) return;

    function buildSuggestions(value) {
        suggestions.innerHTML = "";

        if (value.trim().length < 1) {
            suggestions.classList.add("d-none");
            return;
        }

        let matches = [];

        items.forEach(item => {
            const title = item.dataset.title || "";
            const name = item.dataset.name || "";
            const category = item.dataset.category || "";
            const content = item.dataset.content || "";
            const haystack = `${title} ${name} ${category} ${content}`;

            if (haystack.includes(value.toLowerCase())) {
                matches.push({
                    title: title || "Skill Exchange",
                    name: name || "Member",
                    category: category || "Skill",
                });
            }
        });

        matches = matches.slice(0, 6);

        if (matches.length === 0) {
            suggestions.innerHTML =
                `<div class="suggestion-item text-muted">No quick matches. Press Enter to search.</div>`;
            suggestions.classList.remove("d-none");
            return;
        }

        matches.forEach(match => {
            const div = document.createElement("div");
            div.className = "suggestion-item";
            div.innerHTML = `
                <strong>${match.title}</strong>
                <small>${match.name} · ${match.category}</small>
            `;

            div.addEventListener("click", function () {
                input.value = match.title;
                form.submit();
            });

            suggestions.appendChild(div);
        });

        suggestions.classList.remove("d-none");
    }

    input.addEventListener("input", function () {
        buildSuggestions(this.value);
    });

    input.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            form.submit();
        }
    });

    document.addEventListener("click", function (e) {
        if (!suggestions.contains(e.target) && e.target !== input) {
            suggestions.classList.add("d-none");
        }
    });
});
</script>
<?php
}
