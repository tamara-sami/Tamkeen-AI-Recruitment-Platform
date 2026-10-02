
(function(){

    const modal = document.getElementById('qrModal');
    const openBtn = document.getElementById('openQrScanner');
    const closeBtn = document.getElementById('closeQrScanner');

    const video = document.getElementById('qrVideo');
    const qrInput = document.getElementById('qrSearchInput');
    const form = document.getElementById('candidateSearchForm');

    const manual = document.getElementById('manualQrValue');
    const applyManual = document.getElementById('applyManualQr');
    const status = document.getElementById('qrStatus');

    let stream = null;
    let timer = null;

    function submitQr(value){
        qrInput.value = value;
        form.submit();
    }

    async function openScanner(){

        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');

        if (!('BarcodeDetector' in window)) {
            status.textContent =
            'Your browser does not support live scanning. Paste the QR value manually.';
            return;
        }

        try {

            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment'
                }
            });

            video.srcObject = stream;

            const detector = new BarcodeDetector({
                formats: ['qr_code']
            });

            timer = setInterval(async () => {

                if (video.readyState < 2) return;

                const codes = await detector.detect(video);

                if (codes.length) {
                    submitQr(codes[0].rawValue);
                }

            }, 700);

        } catch(e){

            status.textContent =
            'Camera permission blocked. Paste QR manually.';
        }
    }

    function closeScanner(){

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');

        if (timer) {
            clearInterval(timer);
        }

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    if(openBtn){
        openBtn.addEventListener('click', openScanner);
    }

    if(closeBtn){
        closeBtn.addEventListener('click', closeScanner);
    }

    if(applyManual){

        applyManual.addEventListener('click', () => {

            if(manual.value.trim()){
                submitQr(manual.value.trim());
            }

        });
    }

})();


const searchInput = document.querySelector('input[name="search"]');

if (searchInput) {

    searchInput.addEventListener('input', function () {

        if (this.value.trim() === '') {

            window.location.href = window.location.pathname;

        }

    });

}