

(function () {

    /* ================================
       Interview Modal Elements
       ================================ */

    const modal = document.getElementById('interviewModal');

    const openButtons = [
        document.getElementById('openInterviewModal'),
        document.getElementById('openInterviewModalTop')
    ].filter(Boolean);

    const closeButtons = [
        document.getElementById('closeInterviewModal'),
        document.getElementById('cancelInterviewModal')
    ].filter(Boolean);

    const form = document.getElementById('scheduleInterviewForm');


    /* ================================
       Open / Close Modal
       ================================ */

    function openModal() {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }


    /* ================================
       Modal Events
       ================================ */

    openButtons.forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });

    closeButtons.forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    // Close modal when clicking outside the card
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });


    /* ================================
       Form Validation
       ================================ */

    if (form) {
        form.addEventListener('submit', function (event) {

            const phone = form.querySelector('[name="interviewer_phone"]');
            const date = form.querySelector('[name="interview_date"]');

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Browser validation for required/minlength/pattern fields
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
                return;
            }

            // Jordanian phone validation:
            // must start with 077, 078, or 079 and total length = 10 digits
            if (!/^07[789][0-9]{7}$/.test(phone.value.trim())) {
                event.preventDefault();

                phone.setCustomValidity(
                    'Enter a valid Jordanian phone number: 10 digits starting with 077, 078, or 079.'
                );

                phone.reportValidity();
                phone.setCustomValidity('');
                return;
            }

            // Interview date must not be in the past
            const selectedDate = new Date(date.value + 'T00:00:00');

            if (selectedDate < today) {
                event.preventDefault();

                date.setCustomValidity(
                    'Interview date must be today or in the future.'
                );

                date.reportValidity();
                date.setCustomValidity('');
            }

        });
    }


    /* ================================
       Preserve Scroll Position
       ================================ */

    // Save current scroll before page refresh or form submit
    window.addEventListener('beforeunload', function () {
        localStorage.setItem(
            'interviewsScroll',
            String(window.scrollY)
        );
    });

    // Restore scroll position after reload
    window.addEventListener('load', function () {
        const saved = localStorage.getItem('interviewsScroll');

        if (saved !== null) {
            window.scrollTo({
                top: parseInt(saved, 10),
                behavior: 'instant'
            });

            localStorage.removeItem('interviewsScroll');
        }
    });

})();