<?php


$base_url = $base_url ?? "/tamkeentest/";

if (!str_ends_with($base_url, "/")) {
    $base_url .= "/";
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function qs(selector) {
        return document.querySelector(selector);
    }

    function qsa(selector) {
        return document.querySelectorAll(selector);
    }

    function safeModal(id) {
        const element = document.getElementById(id);

        if (!element || typeof bootstrap === "undefined") {
            return null;
        }

        return new bootstrap.Modal(element);
    }

    function updateCount(id, change) {
        const countEl = document.getElementById(id);

        if (!countEl) return;

        const current = parseInt(countEl.textContent, 10);

        if (!isNaN(current)) {
            countEl.textContent = Math.max(0, current + change);
        }
    }

    /* =========================
       File input preview
    ========================= */

    const taskFile = document.getElementById("taskFile");
    const fileName = document.getElementById("fileName");
    const removeFileBtn = document.getElementById("removeFileBtn");

    if (taskFile && fileName && removeFileBtn) {

        taskFile.addEventListener("change", function () {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                removeFileBtn.classList.remove("d-none");
            }
        });

        removeFileBtn.addEventListener("click", function () {
            taskFile.value = "";
            fileName.textContent = "No file chosen";
            removeFileBtn.classList.add("d-none");
        });
    }

    const taskImage = document.getElementById("taskImage");
    const imagePreview = document.getElementById("imagePreview");
    const imageIcon = document.getElementById("imageIcon");
    const removeImageBtn = document.getElementById("removeImageBtn");

    if (taskImage && imagePreview && imageIcon && removeImageBtn) {

        taskImage.addEventListener("change", function () {
            const file = this.files[0];

            if (file) {
                imagePreview.src = URL.createObjectURL(file);
                imagePreview.classList.remove("d-none");
                imageIcon.classList.add("d-none");
                removeImageBtn.classList.remove("d-none");
            }
        });

        removeImageBtn.addEventListener("click", function () {
            taskImage.value = "";
            imagePreview.src = "";
            imagePreview.classList.add("d-none");
            imageIcon.classList.remove("d-none");
            removeImageBtn.classList.add("d-none");
        });
    }

    /* =========================
       Soft delete task/training
    ========================= */

    let selectedDeleteId = null;
    let selectedDeleteType = "task";

    qsa(".delete-task-btn, .delete-training-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            selectedDeleteId = this.getAttribute("data-id");
            selectedDeleteType = this.classList.contains("delete-training-btn") ? "training" : "task";

            const modal = safeModal("deleteModal");

            if (modal) {
                modal.show();
            }
        });
    });

    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

    if (confirmDeleteBtn) {

        confirmDeleteBtn.addEventListener("click", function () {

            if (!selectedDeleteId) return;

            const actionUrl = selectedDeleteType === "training"
                ? "delete-training.php"
                : "delete-task.php";

            fetch(actionUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id=" + encodeURIComponent(selectedDeleteId)
            })
            .then(res => res.json())
            .then(data => {

                if (data.success) {
                    const row =
                        document.getElementById("task-" + selectedDeleteId) ||
                        document.getElementById("training-" + selectedDeleteId);

                    if (row) {
                        row.remove();
                    }

                    updateCount("tasksCount", -1);
                    updateCount("trainingsCount", -1);

                    const modalElement = document.getElementById("deleteModal");

                    if (modalElement) {
                        const instance = bootstrap.Modal.getInstance(modalElement);
                        if (instance) instance.hide();
                    }

                } else {
                    alert("Error deleting item");
                }

            })
            .catch(() => {
                alert("Connection error");
            });
        });
    }

    /* =========================
       Delete forever
    ========================= */

    let deleteForeverId = null;
    let deleteForeverType = "task";

    qsa(".delete-forever-btn, .delete-training-forever-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            deleteForeverId = this.getAttribute("data-id");
            deleteForeverType = this.classList.contains("delete-training-forever-btn") ? "training" : "task";

            const modal = safeModal("deleteForeverModal");

            if (modal) {
                modal.show();
            }
        });
    });

    const confirmDeleteForeverBtn = document.getElementById("confirmDeleteForeverBtn");

    if (confirmDeleteForeverBtn) {

        confirmDeleteForeverBtn.addEventListener("click", function () {

            if (!deleteForeverId) return;

            const actionUrl = deleteForeverType === "training"
                ? "delete-forever-training.php"
                : "delete-forever-task.php";

            fetch(actionUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id=" + encodeURIComponent(deleteForeverId)
            })
            .then(res => res.json())
            .then(data => {

                if (data.success) {
                    location.reload();
                } else {
                    alert("Error deleting item forever");
                }

            })
            .catch(() => {
                alert("Connection error");
            });
        });
    }

    /* =========================
       Edit task/training modal
    ========================= */

    const editModalElement = document.getElementById("editModal");
    const editModal = editModalElement ? new bootstrap.Modal(editModalElement) : null;

    qsa(".edit-task-btn, .edit-training-btn").forEach(btn => {
        btn.addEventListener("click", function () {

            const map = {
                editId: "id",
                editTitle: "title",
                editDescription: "description",
                editCategory: "category",
                editDifficulty: "difficulty",
                editTime: "time",
                editMinimumFocus: "minimumFocus",
                editPoints: "points",
                editDeadline: "deadline",
                editSkills: "skills",
                editTrainingType: "trainingType",
                editField: "field",
                editLocation: "location",
                editDuration: "duration",
                editStartDate: "startDate",
                editEndDate: "endDate",
                editRequirements: "requirements",
                editSeats: "seats"
            };

            Object.keys(map).forEach(inputId => {
                const input = document.getElementById(inputId);

                if (input) {
                    const key = map[inputId];
                    input.value = this.dataset[key] || "";
                }
            });

            if (editModal) {
                editModal.show();
            }
        });
    });

    const editForm = document.getElementById("editForm");

    if (editForm) {

        editForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            const isTraining = this.dataset.type === "training" || this.classList.contains("training-edit-form");

            const actionUrl = isTraining
                ? "update-training.php"
                : "update-task.php";

            fetch(actionUrl, {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {

                qsa(".text-danger").forEach(el => {
                    el.textContent = "";
                    el.classList.add("d-none");
                });

                if (data.success) {
                    location.reload();
                    return;
                }

                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {

                        const errorEl = document.getElementById("error-" + field);

                        if (errorEl) {
                            errorEl.textContent = data.errors[field];
                            errorEl.classList.remove("d-none");
                        }
                    });
                }
            })
            .catch(() => {
                alert("Connection error");
            });
        });
    }

    /* =========================
       Publish task/training
    ========================= */

    let itemToEditAfterWarning = null;
    let itemToEditType = "task";

    qsa(".publish-task-btn, .publish-training-btn").forEach(btn => {

        btn.addEventListener("click", function () {

            const itemId = this.getAttribute("data-id");
            itemToEditType = this.classList.contains("publish-training-btn") ? "training" : "task";

            const actionUrl = itemToEditType === "training"
                ? "publish-training.php"
                : "publish-task.php";

            fetch(actionUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id=" + encodeURIComponent(itemId)
            })
            .then(res => res.json())
            .then(data => {

                if (data.success) {
                    location.reload();
                    return;
                }

                itemToEditAfterWarning = itemId;

                const modal = safeModal("publishWarningModal");

                if (modal) {
                    modal.show();
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(() => {
                alert("Connection error");
            });
        });
    });

    const continueEditBtn = document.getElementById("continueEditBtn");

    if (continueEditBtn) {

        continueEditBtn.addEventListener("click", function () {

            if (!itemToEditAfterWarning) return;

            const modalElement = document.getElementById("publishWarningModal");

            if (modalElement) {
                const instance = bootstrap.Modal.getInstance(modalElement);
                if (instance) instance.hide();
            }

            const selector = itemToEditType === "training"
                ? '.edit-training-btn[data-id="' + itemToEditAfterWarning + '"]'
                : '.edit-task-btn[data-id="' + itemToEditAfterWarning + '"]';

            const editBtn = document.querySelector(selector);

            if (editBtn) {
                setTimeout(() => {
                    editBtn.click();
                }, 300);
            }
        });
    }

    /* =========================
       Smart search suggestions
    ========================= */

    const searchInput =
        document.getElementById("taskSearch") ||
        document.getElementById("trainingSearch");

    const suggestionsBox =
        document.getElementById("searchSuggestions") ||
        document.getElementById("trainingSearchSuggestions");

    const listItems = document.querySelectorAll(".task-post-item, .training-post-item");

    if (searchInput && suggestionsBox && listItems.length > 0) {

        searchInput.addEventListener("input", function () {

            const keyword = this.value.toLowerCase().trim();

            suggestionsBox.innerHTML = "";

            let matches = [];

            listItems.forEach(item => {

                const title = item.dataset.title || "";
                const field = item.dataset.field || "";
                const category = item.dataset.category || "";

                const haystack = `${title} ${field} ${category}`.toLowerCase();

                if (keyword === "" || haystack.includes(keyword)) {
                    item.style.display = "";
                    if (keyword !== "") {
                        matches.push(title || field || category);
                    }
                } else {
                    item.style.display = "none";
                }
            });

            matches = [...new Set(matches.filter(Boolean))].slice(0, 5);

            if (keyword !== "" && matches.length > 0) {

                suggestionsBox.classList.remove("d-none");

                matches.forEach(title => {

                    const item = document.createElement("div");

                    item.className = "p-3 border-bottom";
                    item.style.cursor = "pointer";
                    item.textContent = title;

                    item.addEventListener("click", function () {

                        searchInput.value = title;

                        suggestionsBox.classList.add("d-none");

                        listItems.forEach(row => {
                            const rowTitle = row.dataset.title || "";
                            row.style.display = rowTitle === title ? "" : "none";
                        });
                    });

                    suggestionsBox.appendChild(item);
                });

            } else {
                suggestionsBox.classList.add("d-none");
            }
        });

        document.addEventListener("click", function (e) {
            if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                suggestionsBox.classList.add("d-none");
            }
        });
    }

});
</script>

</body>
</html>
