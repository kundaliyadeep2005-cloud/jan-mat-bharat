/**
 * face_vote.js
 * Controls the Face ID biometric gate on the voting page.
 * A party must be selected first, then the user scans their face.
 * Only on success is the Submit Vote button unlocked.
 */
document.addEventListener('DOMContentLoaded', () => {
    const faceGate        = document.getElementById('faceGate');
    const faceScanStatus  = document.getElementById('faceScanStatus');
    const faceVideoWrapper= document.getElementById('faceVideoWrapper');
    const voteVideoFeed   = document.getElementById('voteVideoFeed');
    const startFaceScanBtn= document.getElementById('startFaceScanBtn');
    const scanAndVerifyBtn= document.getElementById('scanAndVerifyBtn');
    const submitVoteBtn   = document.getElementById('submitVoteBtn');
    const faceVerifyHint  = document.getElementById('faceVerifyHint');

    let modelsLoaded = false;
    let localStream  = null;
    let faceVerified = false;

    // Expose hook for vote.js to call once a party is selected
    window.onPartySelected = function() {
        if (!faceVerified) {
            faceGate.style.display = 'block';
            faceGate.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    async function loadModels() {
        setStatus("Loading biometric AI models...", "#888");
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            modelsLoaded = true;
            setStatus("Models ready. Launching webcam...", "#2563eb");
        } catch(e) {
            setStatus("Failed to load AI models.", "red");
            console.error(e);
        }
    }

    function setStatus(msg, color = "#555") {
        faceScanStatus.textContent = msg;
        faceScanStatus.style.color = color;
    }

    function stopWebcam() {
        if (localStream) {
            localStream.getTracks().forEach(t => t.stop());
            localStream = null;
        }
        faceVideoWrapper.style.display = 'none';
        scanAndVerifyBtn.classList.add('d-none');
    }

    startFaceScanBtn.addEventListener('click', async () => {
        startFaceScanBtn.disabled = true;
        if (!modelsLoaded) await loadModels();
        if (!modelsLoaded) { startFaceScanBtn.disabled = false; return; }

        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: {} });
            voteVideoFeed.srcObject = localStream;
            faceVideoWrapper.style.display = 'block';
            scanAndVerifyBtn.classList.remove('d-none');
            setStatus("Look directly into the camera.", "#2563eb");
        } catch(err) {
            setStatus("Webcam unavailable. Check browser permissions.", "red");
            startFaceScanBtn.disabled = false;
        }
    });

    scanAndVerifyBtn.addEventListener('click', async () => {
        scanAndVerifyBtn.disabled = true;
        setStatus("Mapping facial geometry...", "#d97706");

        const detection = await faceapi
            .detectSingleFace(voteVideoFeed)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            setStatus("No face detected. Please adjust lighting & try again.", "red");
            scanAndVerifyBtn.disabled = false;
            return;
        }

        const descriptorArray = Array.from(detection.descriptor);
        setStatus("Verifying identity with server...", "#d97706");

        try {
            const response = await fetch('../php/face_verify_vote.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ face_descriptor: descriptorArray })
            });

            const result = await response.json();

            if (result.success) {
                faceVerified = true;
                stopWebcam();
                faceGate.style.display = 'none';

                // Unlock the submit button
                submitVoteBtn.disabled = false;
                submitVoteBtn.style.opacity = '1';
                submitVoteBtn.style.cursor  = 'pointer';
                faceVerifyHint.textContent  = '✅ Identity verified! You may now submit your vote.';
                faceVerifyHint.style.color  = 'green';
                faceVerifyHint.style.fontWeight = 'bold';
            } else {
                setStatus(result.message || "Identity mismatch. Access denied.", "red");
                scanAndVerifyBtn.disabled = false;
            }
        } catch(err) {
            console.error(err);
            setStatus("Server error. Please try again.", "red");
            scanAndVerifyBtn.disabled = false;
        }
    });
});
