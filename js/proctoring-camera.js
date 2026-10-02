document.body.classList.add("camera-locked");

(async function () {

    if (
        typeof window.TAMKEEN_PROCTORING === "undefined" ||
        !window.TAMKEEN_PROCTORING.enabled
    ) {
        return;
    }

    const config = window.TAMKEEN_PROCTORING;

    const openBtn =
        document.getElementById("openCameraBtn");

    const errorText =
        document.getElementById("cameraErrorText");

    const overlay =
        document.getElementById("cameraLockOverlay");

    let stream = null;

    let videoElement = null;

    let lastFaceX = null;

    let noFaceStart = null;

    let lookingAwayStart = null;

    let movementCounter = 0;

    let lastLogged = {};

    async function logEvent(
        type,
        severity = "medium",
        details = ""
    ) {

        const now = Date.now();

        if (
            lastLogged[type] &&
            now - lastLogged[type] < 10000
        ) {
            return;
        }

        lastLogged[type] = now;

        try {

            await fetch(config.endpoint, {
                method: "POST",
                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded"
                },
                body:
                    "attempt_id=" +
                    encodeURIComponent(config.attemptId) +
                    "&event_type=" +
                    encodeURIComponent(type) +
                    "&severity=" +
                    encodeURIComponent(severity) +
                    "&details=" +
                    encodeURIComponent(details)
            });

        } catch (e) {}
    }

    async function enableCamera() {

        try {

            stream =
                await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: false
                });

            showCameraPreview();

            overlay.remove();

            if (
                typeof window.startTaskAfterCamera ===
                "function"
            ) {

                window.startTaskAfterCamera();
            }

            monitorBlackFrames();

            startFaceAI();

            await logEvent(
                "camera_started",
                "low",
                "Camera enabled successfully"
            );

        } catch (e) {

            errorText.textContent =
                "Camera access is required.";

            await logEvent(
                "camera_denied",
                "high",
                e.message
            );
        }
    }

    function showCameraPreview() {

        videoElement =
            document.createElement("video");

        videoElement.srcObject = stream;

        videoElement.autoplay = true;

        videoElement.playsInline = true;

        videoElement.muted = true;

        videoElement.style.position = "fixed";
        videoElement.style.bottom = "15px";
        videoElement.style.right = "15px";
        videoElement.style.width = "220px";
        videoElement.style.height = "160px";
        videoElement.style.borderRadius = "18px";
        videoElement.style.objectFit = "cover";
        videoElement.style.zIndex = "99999";
        videoElement.style.background = "#000";
        videoElement.style.border =
            "3px solid #2563EB";
        videoElement.style.boxShadow =
            "0 10px 40px rgba(0,0,0,.25)";

        document.body.appendChild(videoElement);
    }

    function monitorBlackFrames() {

        setInterval(async () => {

            if (!videoElement) return;

            const dark =
                isFrameDark(videoElement);

            if (dark) {

                await logEvent(
                    "camera_black",
                    "high",
                    "Black camera frame detected"
                );
            }

        }, 5000);
    }

    function isFrameDark(video) {

        const canvas =
            document.createElement("canvas");

        const ctx =
            canvas.getContext("2d");

        canvas.width = 160;
        canvas.height = 120;

        ctx.drawImage(
            video,
            0,
            0,
            canvas.width,
            canvas.height
        );

        const data =
            ctx.getImageData(
                0,
                0,
                canvas.width,
                canvas.height
            ).data;

        let total = 0;

        for (
            let i = 0;
            i < data.length;
            i += 4
        ) {

            total +=
                (
                    data[i] +
                    data[i + 1] +
                    data[i + 2]
                ) / 3;
        }

        const avg =
            total / (data.length / 4);

        return avg < 25;
    }

    function startFaceAI() {

       const faceDetection =
    new FaceDetection({
                locateFile: (file) => {
                    return `https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/${file}`;
                }
            });

        faceDetection.setOptions({
            model: "short",
            minDetectionConfidence: 0.5
        });

        faceDetection.onResults(async (results) => {

            if (!results.detections) return;

            const faces =
                results.detections;

            /*
            |--------------------------------------------------------------------------
            | No Face
            |--------------------------------------------------------------------------
            */

            if (faces.length === 0) {

                if (!noFaceStart) {
                    noFaceStart = Date.now();
                }

                if (
                    Date.now() - noFaceStart >
                    5000
                ) {

                    await logEvent(
                        "no_face_detected",
                        "high",
                        "No face visible"
                    );
                }

                return;

            } else {

                noFaceStart = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Multiple Faces
            |--------------------------------------------------------------------------
            */

            if (faces.length > 1) {

                await logEvent(
                    "multiple_faces",
                    "high",
                    "Multiple faces detected"
                );
            }

            const box =
                faces[0].boundingBox;

            const faceCenterX =
                box.xCenter;

            /*
            |--------------------------------------------------------------------------
            | Looking Away
            |--------------------------------------------------------------------------
            */

            if (
                faceCenterX < 0.30 ||
                faceCenterX > 0.70
            ) {

                if (!lookingAwayStart) {
                    lookingAwayStart =
                        Date.now();
                }

                if (
                    Date.now() -
                    lookingAwayStart >
                    4000
                ) {

                    await logEvent(
                        "looking_away",
                        "medium",
                        "User looking away"
                    );
                }

            } else {

                lookingAwayStart = null;
            }

            /*
            |--------------------------------------------------------------------------
            | High Movement
            |--------------------------------------------------------------------------
            */

            if (lastFaceX !== null) {

                const movement =
                    Math.abs(
                        faceCenterX - lastFaceX
                    );

                if (movement > 0.15) {

                    movementCounter++;

                    if (
                        movementCounter >= 4
                    ) {

                        await logEvent(
                            "high_movement",
                            "medium",
                            "Excessive movement detected"
                        );

                        movementCounter = 0;
                    }
                }
            }

            lastFaceX = faceCenterX;
        });

        const camera =
            new Camera(videoElement, {

                onFrame: async () => {

                    await faceDetection.send({
                        image: videoElement
                    });
                },

                width: 640,
                height: 480
            });

        camera.start();
    }

    if (openBtn) {

        openBtn.addEventListener(
            "click",
            enableCamera
        );
    }

})();