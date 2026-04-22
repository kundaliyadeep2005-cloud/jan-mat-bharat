document.addEventListener('DOMContentLoaded', async () => {
    const videoFeed = document.getElementById('loginVideoFeed');
    const faceAuthContainer = document.getElementById('faceAuthContainer');
    const startFaceLoginBtn = document.getElementById('startFaceLoginBtn');
    const cancelFaceBtn = document.getElementById('cancelFaceBtn');
    const verifyFaceBtn = document.getElementById('verifyFaceBtn');
    const faceAuthStatus = document.getElementById('faceAuthStatus');
    const normalLoginBtn = document.getElementById('normalLoginBtn');
    
    let modelsLoaded = false;
    let localStream = null;

    async function loadModels() {
        faceAuthStatus.textContent = "Booting Face ID array...";
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            modelsLoaded = true;
            faceAuthStatus.textContent = "Array active. Mounting webcam...";
        } catch (e) {
            faceAuthStatus.textContent = "Failed mapping models.";
            console.error(e);
        }
    }

    function stopWebcam() {
        if(localStream) {
            localStream.getTracks().forEach(track => track.stop());
            localStream = null;
        }
        faceAuthContainer.classList.add('d-none');
        startFaceLoginBtn.disabled = false;
        normalLoginBtn.disabled = false;
        verifyFaceBtn.classList.add('d-none');
    }

    startFaceLoginBtn.addEventListener('click', async () => {
        startFaceLoginBtn.disabled = true;
        normalLoginBtn.disabled = true;
        faceAuthContainer.classList.remove('d-none');

        if (!modelsLoaded) {
            await loadModels();
        }
        
        if (!modelsLoaded) {
            stopWebcam();
            return;
        }
        
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: {} });
            videoFeed.srcObject = localStream;
            verifyFaceBtn.classList.remove('d-none');
            faceAuthStatus.textContent = "Look into the camera directly.";
            faceAuthStatus.className = "small fw-bold text-primary";
        } catch (err) {
            faceAuthStatus.textContent = "Webcam offline. Check permissions.";
            faceAuthStatus.className = "small fw-bold text-danger";
            stopWebcam();
        }
    });

    cancelFaceBtn.addEventListener('click', () => {
        stopWebcam();
    });

    verifyFaceBtn.addEventListener('click', async () => {
        faceAuthStatus.textContent = "Scanning geometric grid...";
        faceAuthStatus.className = "small fw-bold text-warning";
        verifyFaceBtn.disabled = true;

        const detections = await faceapi.detectSingleFace(videoFeed).withFaceLandmarks().withFaceDescriptor();

        if (detections) {
            faceAuthStatus.textContent = "Face locked. Authenticating securely...";
            
            const descriptorArray = Array.from(detections.descriptor);
            const payload = JSON.stringify({ face_descriptor: descriptorArray });

            try {
                // Post to our PHP verification endpoint
                const response = await fetch('../php/face_login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: payload
                });

                const result = await response.json();
                
                if (result.success) {
                    faceAuthStatus.textContent = "Biometric Match Confirmed!";
                    faceAuthStatus.className = "small fw-bold text-success";
                    stopWebcam();
                    // Relocate to dashboard
                    window.location.href = result.redirect;
                } else {
                    faceAuthStatus.textContent = result.message || "Unrecognized face. Access Denied.";
                    faceAuthStatus.className = "small fw-bold text-danger";
                    verifyFaceBtn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                faceAuthStatus.textContent = "Server communication failure.";
                faceAuthStatus.className = "small fw-bold text-danger";
                verifyFaceBtn.disabled = false;
            }

        } else {
            faceAuthStatus.textContent = "Failed. Could not lock onto face.";
            faceAuthStatus.className = "small fw-bold text-danger";
            verifyFaceBtn.disabled = false;
        }
    });
});
