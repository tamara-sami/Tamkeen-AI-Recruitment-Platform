<?php

function renderTaskWorkspacePage($page)
{
    extract($page);
?>
<main class="focus-page">
    <section class="focus-shell">

        <div class="focus-header">
            <div>
                <span class="focus-badge">
                    <i class="fa fa-shield-alt"></i>
                    Focus Mode
                </span>
                <h1><?= htmlspecialchars($task['title']) ?></h1>
                <p>
                    <?= htmlspecialchars($task['company_name'] ?? 'Tamkeen Company') ?> ·
                    <?= htmlspecialchars($task['category']) ?> ·
                    <?= htmlspecialchars($task['difficulty']) ?>
                </p>
            </div>

            <div class="timer-card">
                <small>Minimum focus time</small>
                <strong id="timerText">--:--</strong>
                <span id="timerStatus">
                    <?= $remainingSeconds > 0 ? 'Submission locked' : 'Submission unlocked' ?>
                </span>
            </div>
        </div>

        <div class="exam-warning">
            <div>
                <strong>Exam Mode Rules</strong>
                <p>
                    Switching tabs, leaving this page, copy/paste, or right click will count as violations.
                    Camera AI flags are recorded separately for HR/evaluator review.
                </p>
            </div>

            <div class="violation-pill">
                Violations:
                <strong id="violationCount"><?= (int)$attempt['violation_count'] ?></strong>/5
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger rounded-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <section class="task-reading-card full-width-card">
            <?php if (!empty($task['task_image'])): ?>
                <img src="../uploads/tasks/<?= htmlspecialchars($task['task_image']) ?>" class="focus-task-img" alt="">
            <?php endif; ?>

            <div class="task-info-box">
                <span class="badge bg-primary rounded-pill mb-3">Task Brief</span>

                <h2 class="fw-bold mb-3">
                    <?= htmlspecialchars($task['title']) ?>
                </h2>

                <p class="text-muted task-description">
                    <?= nl2br(htmlspecialchars($task['description'])) ?>
                </p>

                <div class="d-flex flex-wrap gap-2 mt-4 mb-4">
                    <span class="tag"><?= htmlspecialchars($task['category']) ?></span>
                    <span class="tag"><?= htmlspecialchars($task['difficulty']) ?></span>
                    <span class="tag"><?= htmlspecialchars($task['estimated_time']) ?></span>
                    <span class="tag">+<?= htmlspecialchars($task['points']) ?> pts</span>
                </div>

                <?php if (!empty($task['required_skills'])): ?>
                    <div class="required-skills-box">
                        <strong>Required skills:</strong>
                        <?= htmlspecialchars($task['required_skills']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($task['task_file'])): ?>
                    <div class="attachment-box mt-4">
                        <div>
                            <strong>Task Attachment</strong>
                            <p class="text-muted small mb-0">Open the attachment before solving.</p>
                        </div>

                        <button type="button" class="btn btn-primary rounded-pill px-4" id="viewTaskFileBtn">
                            View File
                        </button>
                    </div>

                    <div class="file-viewer-box d-none mt-4" id="taskFileViewer">
                        <div class="file-viewer-header">
                            <strong>Task Attachment</strong>
                            <button type="button" id="closeTaskFileBtn">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <iframe src="../uploads/tasks/<?= htmlspecialchars($task['task_file']) ?>" class="task-file-frame"></iframe>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="workspace-card">
            <div class="workspace-topbar">
                <div>
                    <h3 class="fw-bold mb-1">Workspace</h3>
                    <p class="text-muted mb-0">Write, draw, calculate,.</p>
                </div>

                <div class="workspace-meta">
                    <div class="workspace-mini-card">
                        <small>Task Time Left</small>
                        <strong id="totalTimerText">--:--</strong>
                    </div>

                    <div class="workspace-mini-card">
                        <small>Autosave</small>
                        <strong id="autosaveStatus">Not saved yet</strong>
                    </div>
                </div>
            </div>

            <div class="workspace-tabs">
                <button type="button" class="workspace-tab active-workspace-tab" data-tab="writing">Writing</button>
                <button type="button" class="workspace-tab" data-tab="drawing">Drawing</button>
                <button type="button" class="workspace-tab" data-tab="calculator">Calculator</button>
            </div>

            <form method="POST" enctype="multipart/form-data" id="submissionForm">
                <input type="hidden" name="drawing_image" id="drawingImage">

                <div id="writing" class="workspace-content active-workspace-content">
                    <textarea
                        name="answer_text"
                        class="workspace-textarea"
                        placeholder="Write your solution here..."
                        id="answerText"><?= htmlspecialchars($attempt['autosave_text'] ?? '') ?></textarea>
                </div>

                <div id="drawing" class="workspace-content">
                    <div class="paint-toolbar">
                        <div class="paint-tool-group">
                            <button type="button" class="paint-tool active-paint-tool" data-tool="pen">Pen</button>
                            <button type="button" class="paint-tool" data-tool="eraser">Eraser</button>
                            <button type="button" class="paint-tool" data-tool="line">Line</button>
                            <button type="button" class="paint-tool" data-tool="rect">Rectangle</button>
                            <button type="button" class="paint-tool" data-tool="circle">Circle</button>
                            <button type="button" class="paint-tool" data-tool="text">Text</button>
                        </div>

                        <div class="paint-tool-group">
                            <label class="paint-label">Color</label>
                            <input type="color" id="paintColor" value="#2563EB">
                            <label class="paint-label">Size</label>
                            <input type="range" id="brushSize" min="1" max="30" value="4">
                            <span id="brushSizeText">4</span>
                        </div>

                        <div class="paint-tool-group">
                            <button type="button" class="paint-action" id="undoCanvasBtn">Undo</button>
                            <button type="button" class="paint-action danger" id="clearCanvasBtn">Clear</button>
                        </div>
                    </div>
<div class="draw-text-tools">

    <input
        type="text"
        id="canvasTextInput"
        placeholder="Write text...">

    <button
    type="button"
    onclick="document.querySelector('[data-tool=text]').click();">
    Add Text
</button>

</div>
                    <div class="canvas-wrap">
                        <canvas id="workspaceCanvas" width="1200" height="650"></canvas>
                    </div>
                </div>

                <div id="calculator" class="workspace-content">
                    <div class="calculator-box">
                        <input type="text" id="calcDisplay" class="calc-display" readonly>

                        <div class="calc-buttons">
                            <button type="button" data-calc="7">7</button>
                            <button type="button" data-calc="8">8</button>
                            <button type="button" data-calc="9">9</button>
                            <button type="button" data-calc="/">÷</button>
                            <button type="button" data-calc="4">4</button>
                            <button type="button" data-calc="5">5</button>
                            <button type="button" data-calc="6">6</button>
                            <button type="button" data-calc="*">×</button>
                            <button type="button" data-calc="1">1</button>
                            <button type="button" data-calc="2">2</button>
                            <button type="button" data-calc="3">3</button>
                            <button type="button" data-calc="-">-</button>
                            <button type="button" data-calc="0">0</button>
                            <button type="button" id="calcEquals">=</button>
                            <button type="button" id="calcClear">C</button>
                            <button type="button" data-calc="+">+</button>
                        </div>
                    </div>
                </div>

             

                <div class="workspace-submit-bar">
                    <div class="submit-meta">
                        <div>
                            <small>Started at</small>
                            <strong><?= htmlspecialchars($attempt['started_at']) ?></strong>
                        </div>

                        <div>
                            <small>Minimum minutes</small>
                            <strong><?= (int)$attempt['minimum_minutes'] ?> minutes</strong>
                        </div>
                    </div>

                    <div class="submit-actions">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submitBtn" <?= $remainingSeconds > 0 ? 'disabled' : '' ?>>
                            Submit Task
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </section>
   
</main>
 <div id="cameraLockOverlay">

    <div class="camera-lock-card">

        <div class="camera-lock-icon">
            <i class="fa fa-video"></i>
        </div>

        <h2>Camera Required</h2>

        <p>
            You must enable your camera before starting this task.
            AI proctoring is required during the exam.
        </p>

        <button id="openCameraBtn">
            Open Camera
        </button>

        <div id="cameraErrorText"></div>

    </div>

</div>

<style>

body.camera-locked {
    overflow: hidden;
}

body.camera-locked .focus-page {
    filter: blur(10px);
    pointer-events: none;
    user-select: none;
}

#cameraLockOverlay{
    position:fixed;
    inset:0;
    z-index:999999;
    background:rgba(15,23,42,.72);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.camera-lock-card{
    width:100%;
    max-width:430px;
    background:#fff;
    border-radius:28px;
    padding:35px;
    text-align:center;
    box-shadow:0 25px 90px rgba(0,0,0,.25);
    font-family:Inter,sans-serif;
}

.camera-lock-icon{
    width:85px;
    height:85px;
    margin:0 auto 20px;
    border-radius:50%;
    background:#EEF2FF;
    color:#2563EB;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
}

.camera-lock-card h2{
    font-weight:800;
    margin-bottom:12px;
    color:#0F172A;
}

.camera-lock-card p{
    color:#64748B;
    line-height:1.7;
    margin-bottom:25px;
}

#openCameraBtn{
    border:none;
    background:#2563EB;
    color:#fff;
    width:100%;
    border-radius:18px;
    padding:16px;
    font-weight:700;
    font-size:16px;
    cursor:pointer;
}

#openCameraBtn:hover{
    opacity:.95;
}

#cameraErrorText{
    margin-top:16px;
    color:#DC2626;
    font-weight:600;
    font-size:14px;
}

</style>
<script>
window.TAMKEEN_PROCTORING = {
    enabled: true,
    attemptId: <?= (int)$attempt['id'] ?>,
    endpoint: "record-proctoring-event.php",
    redirectUrl: "tasks.php?camera_required=1"
};
</script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>

<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/face_detection.js"></script>

<script src="../../js/proctoring-camera.js"></script>

<script>
(function () {
    const attemptId = <?= (int)$attempt['id'] ?>;
    let remainingSeconds = <?= (int)$remainingSeconds ?>;
    let totalRemainingSeconds = <?= (int)$totalRemainingSeconds ?>;
    let violationCount = <?= (int)$attempt['violation_count'] ?>;

    let disqualified = false;
    let cameraVerified = false;
    let autosaveStarted = false;
    let leavingBecauseSubmit = false;
    let fileDialogOpen = false;
    let autoSubmitting = false;
    let drawingHasContent = false;

    const timerText = document.getElementById("timerText");
    const totalTimerText = document.getElementById("totalTimerText");
    const timerStatus = document.getElementById("timerStatus");
    const violationCountBox = document.getElementById("violationCount");
    const submitBtn = document.getElementById("submitBtn");
    const answerText = document.getElementById("answerText");
    const answerFile = document.getElementById("answerFile");
    const submissionForm = document.getElementById("submissionForm");
    const autosaveStatus = document.getElementById("autosaveStatus");
    const drawingImage = document.getElementById("drawingImage");

    const viewTaskFileBtn = document.getElementById("viewTaskFileBtn");
    const taskFileViewer = document.getElementById("taskFileViewer");
    const closeTaskFileBtn = document.getElementById("closeTaskFileBtn");

    function formatTime(seconds) {
        seconds = Math.max(0, seconds);
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;

        if (h > 0) {
            return String(h).padStart(2, "0") + ":" + String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
        }

        return String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    }

    function unlockActions() {
        if (submitBtn) submitBtn.disabled = false;
        if (timerStatus) timerStatus.textContent = "Submission unlocked";
    }

    function updateMinimumTimer() {
        if (timerText) timerText.textContent = formatTime(remainingSeconds);

        if (remainingSeconds <= 0) {
            unlockActions();
            return;
        }

        remainingSeconds--;
        setTimeout(updateMinimumTimer, 1000);
    }

    function updateTotalTimer() {
        if (totalTimerText) totalTimerText.textContent = formatTime(totalRemainingSeconds);

        if (totalRemainingSeconds <= 0) {
            autoSubmitNow();
            return;
        }

        totalRemainingSeconds--;
        setTimeout(updateTotalTimer, 1000);
    }

    window.startTaskAfterCamera = function () {

        cameraVerified = true;

        document.body.classList.remove("camera-locked");

        updateMinimumTimer();

        updateTotalTimer();

        if (!autosaveStarted) {
            autosaveStarted = true;
            setInterval(autosaveNow, 10000);
        }
    };

    document.querySelectorAll(".workspace-tab").forEach(button => {
        button.addEventListener("click", function () {
            document.querySelectorAll(".workspace-tab").forEach(btn => btn.classList.remove("active-workspace-tab"));
            document.querySelectorAll(".workspace-content").forEach(content => content.classList.remove("active-workspace-content"));

            this.classList.add("active-workspace-tab");
            const content = document.getElementById(this.dataset.tab);
            if (content) content.classList.add("active-workspace-content");
        });
    });

    const canvas = document.getElementById("workspaceCanvas");
    const ctx = canvas ? canvas.getContext("2d") : null;
    const colorInput = document.getElementById("paintColor");
    const brushInput = document.getElementById("brushSize");
    const brushSizeText = document.getElementById("brushSizeText");
    const undoBtn = document.getElementById("undoCanvasBtn");
    const clearBtn = document.getElementById("clearCanvasBtn");

    let currentTool = "pen";
    let isDrawing = false;
    let startX = 0;
    let startY = 0;
    let canvasSnapshot = null;
    const undoStack = [];

    function saveUndoState() {
        if (!ctx || !canvas) return;
        if (undoStack.length > 20) undoStack.shift();
        undoStack.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
    }

    function getCanvasPoint(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function applyBrush() {
        if (!ctx) return;
        ctx.lineWidth = Number(brushInput?.value || 4);
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
        ctx.strokeStyle = colorInput?.value || "#2563EB";
        ctx.fillStyle = colorInput?.value || "#2563EB";
    }

    document.querySelectorAll(".paint-tool").forEach(button => {
        button.addEventListener("click", function () {
            document.querySelectorAll(".paint-tool").forEach(btn => btn.classList.remove("active-paint-tool"));
            this.classList.add("active-paint-tool");
            currentTool = this.dataset.tool;
        });
    });

    if (brushInput && brushSizeText) {
        brushInput.addEventListener("input", function () {
            brushSizeText.textContent = this.value;
        });
    }

    function startDrawing(e) {
        if (!canvas || !ctx) return;
        e.preventDefault();
        const point = getCanvasPoint(e);
        startX = point.x;
        startY = point.y;

        if (currentTool === "text") {
           const text =
    document.getElementById("canvasTextInput").value;

if (text.trim()) {

    saveUndoState();

    applyBrush();

    ctx.font =
        `${Math.max(16, Number(brushInput.value))}px Arial`;

    ctx.fillText(text, startX, startY);

    drawingHasContent = true;
}

return;
        }

        saveUndoState();
        canvasSnapshot = ctx.getImageData(0, 0, canvas.width, canvas.height);
        isDrawing = true;
        applyBrush();
        ctx.beginPath();
        ctx.moveTo(startX, startY);
    }

    function drawMove(e) {
        if (!isDrawing || !canvas || !ctx) return;
        e.preventDefault();
        const point = getCanvasPoint(e);
        applyBrush();

        if (currentTool === "pen") {
            ctx.globalCompositeOperation = "source-over";
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
            drawingHasContent = true;
            return;
        }

        if (currentTool === "eraser") {
            ctx.globalCompositeOperation = "destination-out";
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
            ctx.globalCompositeOperation = "source-over";
            drawingHasContent = true;
            return;
        }

        if (canvasSnapshot) {
            ctx.putImageData(canvasSnapshot, 0, 0);
        }

        ctx.globalCompositeOperation = "source-over";
        ctx.beginPath();

        if (currentTool === "line") {
            ctx.moveTo(startX, startY);
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
        }

        if (currentTool === "rect") {
            ctx.strokeRect(startX, startY, point.x - startX, point.y - startY);
        }

        if (currentTool === "circle") {
            const radius = Math.hypot(point.x - startX, point.y - startY);
            ctx.arc(startX, startY, radius, 0, Math.PI * 2);
            ctx.stroke();
        }
    }

    function stopDrawing() {
        if (!isDrawing) return;
        isDrawing = false;
        drawingHasContent = true;
        if (ctx) ctx.beginPath();
    }

    if (canvas) {
        canvas.addEventListener("mousedown", startDrawing);
        canvas.addEventListener("mousemove", drawMove);
        canvas.addEventListener("mouseup", stopDrawing);
        canvas.addEventListener("mouseleave", stopDrawing);
        canvas.addEventListener("touchstart", startDrawing, {passive: false});
        canvas.addEventListener("touchmove", drawMove, {passive: false});
        canvas.addEventListener("touchend", stopDrawing);
    }

    if (undoBtn && ctx && canvas) {
        undoBtn.addEventListener("click", function () {
            const last = undoStack.pop();
            if (last) ctx.putImageData(last, 0, 0);
        });
    }

    if (clearBtn && ctx && canvas) {
        clearBtn.addEventListener("click", function () {
            saveUndoState();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawingHasContent = false;
        });
    }

    const calcDisplay = document.getElementById("calcDisplay");
    document.querySelectorAll("[data-calc]").forEach(button => {
        button.addEventListener("click", function () {
            if (calcDisplay) calcDisplay.value += this.dataset.calc;
        });
    });

    const calcEquals = document.getElementById("calcEquals");
    if (calcEquals && calcDisplay) {
        calcEquals.addEventListener("click", function () {
            try {
                if (!/^[0-9+\-*/().\s]+$/.test(calcDisplay.value)) {
                    calcDisplay.value = "Error";
                    return;
                }
                calcDisplay.value = Function("return " + calcDisplay.value)();
            } catch (e) {
                calcDisplay.value = "Error";
            }
        });
    }

    const calcClear = document.getElementById("calcClear");
    if (calcClear && calcDisplay) {
        calcClear.addEventListener("click", function () {
            calcDisplay.value = "";
        });
    }

    function autosaveNow() {
        if (disqualified || autoSubmitting || !answerText || !autosaveStatus) return;

        autosaveStatus.textContent = "Saving...";

        fetch("autosave-task.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "attempt_id=" + encodeURIComponent(attemptId) +
                  "&answer_text=" + encodeURIComponent(answerText.value)
        })
        .then(res => res.json())
        .then(data => {
            autosaveStatus.textContent = data.success ? "Saved" : "Save failed";
        })
        .catch(() => {
            autosaveStatus.textContent = "Save failed";
        });
    }

    if (answerText && autosaveStatus) {
        answerText.addEventListener("input", function () {
            autosaveStatus.textContent = "Unsaved changes";
        });
    }


    function autoSubmitNow() {
        if (autoSubmitting || disqualified) return;
        autoSubmitting = true;
        leavingBecauseSubmit = true;

        if (drawingImage && canvas && drawingHasContent) {
            drawingImage.value = canvas.toDataURL("image/png");
        }

        fetch("auto-submit-task.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "attempt_id=" + encodeURIComponent(attemptId) +
                  "&answer_text=" + encodeURIComponent(answerText ? answerText.value : "")
        })
        .then(res => res.json())
        .then(data => {
            window.location.href = data.redirect || "tasks.php?auto_submitted=1";
        })
        .catch(() => {
            window.location.href = "tasks.php?auto_submitted=1";
        });
    }


    if (answerFile) {
        answerFile.addEventListener("click", function () {
            fileDialogOpen = true;
            setTimeout(() => fileDialogOpen = false, 5000);
        });
    }

    if (viewTaskFileBtn && taskFileViewer) {
        viewTaskFileBtn.addEventListener("click", function () {
            taskFileViewer.classList.remove("d-none");
        });
    }

    if (closeTaskFileBtn && taskFileViewer) {
        closeTaskFileBtn.addEventListener("click", function () {
            taskFileViewer.classList.add("d-none");
        });
    }

    function showViolationWarning(count) {
        const box = document.createElement("div");
        box.className = "focus-toast";
        box.innerHTML = `<strong>Violation ${count}/5</strong><span>Restricted behavior detected.</span>`;
        document.body.appendChild(box);
        setTimeout(() => box.remove(), 3500);
    }

    function recordViolation(type) {
        if (!cameraVerified) return;

        if (disqualified || leavingBecauseSubmit || fileDialogOpen || autoSubmitting) return;

        fetch("record-task-violation.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "attempt_id=" + encodeURIComponent(attemptId) + "&type=" + encodeURIComponent(type)
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            violationCount = data.violations;
            if (violationCountBox) violationCountBox.textContent = violationCount;
            showViolationWarning(violationCount);

            if (data.status === "disqualified") {
                disqualified = true;
showDisqualifiedModal();
            }
        })
        .catch(() => {});
    }

    document.addEventListener("visibilitychange", function () {
        if (document.hidden) recordViolation("tab_switch");
    });

    window.addEventListener("blur", function () {
        recordViolation("tab_switch");
    });

    document.addEventListener("copy", function (e) {
    if (document.activeElement && document.activeElement.id === "canvasTextInput") {
        return;
    }

    e.preventDefault();
    recordViolation("copy_paste");
});

document.addEventListener("paste", function (e) {
    if (document.activeElement && document.activeElement.id === "canvasTextInput") {
        return;
    }

    e.preventDefault();
    recordViolation("copy_paste");
});

    document.addEventListener("contextmenu", function (e) {
        e.preventDefault();
        recordViolation("right_click");
    });

    history.pushState(null, null, location.href);

    window.addEventListener("popstate", function () {
        history.pushState(null, null, location.href);
        recordViolation("leave");
    });

    window.addEventListener("beforeunload", function () {
        if (!cameraVerified) return;

        if (!leavingBecauseSubmit && !disqualified && !fileDialogOpen && !autoSubmitting) {
            navigator.sendBeacon(
                "record-task-violation.php",
                new URLSearchParams({
                    attempt_id: attemptId,
                    type: "leave"
                })
            );
        }
    });

    if (submissionForm) {
        submissionForm.addEventListener("submit", function () {
            leavingBecauseSubmit = true;
            if (drawingImage && canvas && drawingHasContent) {
                drawingImage.value = canvas.toDataURL("image/png");
            }
        });
    }
    function showDisqualifiedModal(){

    const modal = document.createElement("div");

    modal.innerHTML = `
    
    <div class="tamkeen-modal-overlay">

        <div class="tamkeen-modal">

            <div class="tamkeen-modal-icon">
                <i class="fa fa-shield-alt"></i>
            </div>

            <h2>Attempt Disqualified</h2>

            <p>
                You reached the maximum number of violations.
                Your task attempt has been closed automatically.
            </p>

            <button id="closeDisqualifiedModal">
                Return To Tasks
            </button>

        </div>

    </div>
    
    `;

    document.body.appendChild(modal);

    document
        .getElementById("closeDisqualifiedModal")
        .addEventListener("click", function(){

            window.location.href =
                "tasks.php?blocked=1";

        });
}
})();

</script>

<?php
}
