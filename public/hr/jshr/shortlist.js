/**
 * shortlist.js
 * Frontend JavaScript for HR shortlist page.
 * We moved all inline scripts from shortlist.php into this separate JS file
 * so the PHP file stays cleaner and easier to maintain.
 */

/* =========================================================
   Copy Interview Template
   ========================================================= */

document.getElementById('copyInterviewTemplate')?.addEventListener('click', async function () {

    // Get template text from the template box
    const template =
        document.getElementById('interviewTemplateText')
        ?.innerText.trim() || '';

    try {

        // Copy text to clipboard
        await navigator.clipboard.writeText(template);

        // Change button state after copy
        this.innerHTML = '<i class="fa fa-check"></i> Copied';

        // Return original text after 1.4 seconds
        setTimeout(() => {
            this.innerHTML =
                '<i class="fa fa-copy"></i> Copy Template';
        }, 1400);

    } catch (e) {

        // Fallback if clipboard fails
        alert('Copy failed. Please copy manually.');

    }
});


/* =========================================================
   Remove Candidate Modal
   ========================================================= */

(function () {

    // Modal elements
    const modal = document.getElementById('removeShortlistModal');
    const modalText = document.getElementById('removeModalText');

    const cancelBtn = document.getElementById('cancelRemoveModal');
    const closeBtn = document.getElementById('closeRemoveModal');
    const confirmBtn = document.getElementById('confirmRemoveModal');

    // Store selected form before submit
    let selectedForm = null;

    /**
     * Open modal
     */
    function openModal(form, candidateName) {

        selectedForm = form;

        modalText.textContent = candidateName
            ? `Are you sure you want to remove ${candidateName} from your shortlist?`
            : 'Are you sure you want to remove this candidate from your shortlist?';

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    /**
     * Close modal
     */
    function closeModal() {

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');

        selectedForm = null;
    }

    /**
     * Open modal buttons
     */
    document.querySelectorAll('.js-open-remove-modal')
        .forEach((button) => {

            button.addEventListener('click', function () {

                openModal(
                    this.closest('form'),
                    this.dataset.candidateName || ''
                );

            });

        });

    /**
     * Confirm remove
     */
    confirmBtn.addEventListener('click', function () {

        // Submit backend form
        if (selectedForm) {
            selectedForm.submit();
        }

    });

    /**
     * Cancel buttons
     */
    cancelBtn.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    /**
     * Close when clicking outside modal
     */
    modal.addEventListener('click', function (event) {

        if (event.target === modal) {
            closeModal();
        }

    });

    /**
     * Close with ESC button
     */
    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            modal.classList.contains('open')
        ) {
            closeModal();
        }

    });

})();


/* =========================================================
   Preserve Scroll Position
   ========================================================= */

/**
 * Save scroll before refresh/navigation
 */
window.addEventListener("beforeunload", function () {

    localStorage.setItem(
        "shortlistScroll",
        window.scrollY
    );

});

/**
 * Restore scroll after reload
 */
window.addEventListener("load", function () {

    const scrollPosition =
        localStorage.getItem("shortlistScroll");

    if (scrollPosition !== null) {

        window.scrollTo({
            top: parseInt(scrollPosition),
            behavior: "instant"
        });

        localStorage.removeItem("shortlistScroll");

    }

});