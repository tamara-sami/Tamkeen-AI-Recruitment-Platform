<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?= $base_url ?? '' ?>lib/wow/wow.min.js"></script>
<script src="<?= $base_url ?? '' ?>lib/easing/easing.min.js"></script>
<script src="<?= $base_url ?? '' ?>lib/waypoints/waypoints.min.js"></script>
<script src="<?= $base_url ?? '' ?>lib/counterup/counterup.min.js"></script>
<script src="<?= $base_url ?? '' ?>lib/owlcarousel/owl.carousel.min.js"></script>
<script src="<?= $base_url ?? '' ?>js/main.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const links = document.querySelectorAll(".navbar .nav-link");

    function setActive() {
        const hash = window.location.hash;

        links.forEach(link => link.classList.remove("active"));

        if (!hash) {
            
            document.querySelector('.nav-link[href="index.php"]')?.classList.add("active");
        } else {
         
            links.forEach(link => {
                if (link.getAttribute("href").includes(hash)) {
                    link.classList.add("active");
                }
            });
        }
    }

    setActive();

    
    links.forEach(link => {
        link.addEventListener("click", function () {
            links.forEach(l => l.classList.remove("active"));
            this.classList.add("active");
        });
    });

    
    window.addEventListener("hashchange", setActive);

});
</script>
<script>
const input = document.getElementById("taskImage");
const preview = document.getElementById("previewImg");
const wrapper = document.getElementById("imagePreviewWrapper");
const uploadContent = document.getElementById("uploadContent");

if (input && preview && wrapper && uploadContent) {
    input.addEventListener("change", function(e){
        const file = e.target.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(){
                preview.src = reader.result;
                wrapper.classList.remove("d-none");
                uploadContent.classList.add("d-none");
            }
            reader.readAsDataURL(file);
        }
    });
}

function editImage(){
    if (input) input.click();
}

function deleteImage(){
    if (input && preview && wrapper && uploadContent) {
        input.value = "";
        preview.src = "";
        wrapper.classList.add("d-none");
        uploadContent.classList.remove("d-none");
    }
}
</script>

<script>
const fileInput = document.getElementById("taskFile");
const fileName = document.getElementById("fileName");
const editBtn = document.getElementById("editFileBtn");
const deleteBtn = document.getElementById("deleteFileBtn");

if (fileInput && fileName && editBtn && deleteBtn) {
    fileInput.addEventListener("change", function(){
        if(this.files.length > 0){
            fileName.textContent = this.files[0].name;
            fileName.classList.remove("d-none");
            editBtn.classList.remove("d-none");
            deleteBtn.classList.remove("d-none");
        }
    });
}

function editFile(){
    if (fileInput) fileInput.click();
}

function deleteFile(){
    if (fileInput && fileName && editBtn && deleteBtn) {
        fileInput.value = "";
        fileName.textContent = "";
        fileName.classList.add("d-none");
        editBtn.classList.add("d-none");
        deleteBtn.classList.add("d-none");
    }
}
</script>

<script>
const form = document.querySelector("form");
const password = document.getElementById("password");
const error = document.getElementById("passwordError");

if (form && password && error) {
    form.addEventListener("submit", function(e) {
        const pass = password.value;
        const strongPassword = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W]).{8,}$/;

        if (!strongPassword.test(pass)) {
            e.preventDefault();
            error.classList.remove("d-none");
        } else {
            error.classList.add("d-none");
        }
    });
}
</script>
<script>
setTimeout(() => {
    let box = document.getElementById("successBox");
    if (box) {
        box.style.transition = "0.5s";
        box.style.opacity = "0";
        setTimeout(() => box.remove(), 500);
    }
}, 10000); // 10 seconds
</script>



<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("homeSmartSearchForm");
    const input = document.getElementById("homeSmartSearchInput");
    const results = document.getElementById("homeSmartSearchResults");
    const wrap = document.querySelector(".smart-search-wrap");
    const tagLinks = document.querySelectorAll("[data-smart-query]");

    if (!form || !input || !results || !wrap) return;

    let timer = null;
    let controller = null;

    function hideResults() {
        results.innerHTML = "";
        results.classList.add("d-none");
        wrap.classList.remove("has-results");
    }

    function showResults(html) {
        const cleanHtml = (html || "").trim();

        if (input.value.trim() === "" || cleanHtml === "") {
            hideResults();
            return;
        }

        results.innerHTML = cleanHtml;
        results.classList.remove("d-none");
        wrap.classList.add("has-results");
    }

    function runSmartSearch() {
        const query = input.value.trim();

        if (query === "") {
            hideResults();
            return;
        }

        if (controller) controller.abort();
        controller = new AbortController();

        fetch("smart-search.php?q=" + encodeURIComponent(query), {
            signal: controller.signal,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(response => response.text())
        .then(showResults)
        .catch(error => {
            if (error.name !== "AbortError") hideResults();
        });
    }

    input.addEventListener("input", function () {
        clearTimeout(timer);

        if (this.value.trim() === "") {
            hideResults();
            return;
        }

        timer = setTimeout(runSmartSearch, 220);
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        runSmartSearch();
    });

    tagLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();
            input.value = this.dataset.smartQuery || "";
            runSmartSearch();
            input.focus();
        });
    });

    document.addEventListener("click", function (e) {
        if (!wrap.contains(e.target)) {
            results.classList.add("d-none");
        }
    });

    input.addEventListener("focus", function () {
        if (results.innerHTML.trim() !== "") {
            results.classList.remove("d-none");
        }
    });
});
</script>

</body>
</html>