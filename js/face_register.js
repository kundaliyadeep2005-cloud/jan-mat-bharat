document.addEventListener('DOMContentLoaded', async () => {
    const videoFeed = document.getElementById('videoFeed');
    const videoContainer = document.getElementById('videoContainer');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const captureFaceBtn = document.getElementById('captureFaceBtn');
    const faceStatus = document.getElementById('faceStatus');
    const faceDescriptorInput = document.getElementById('face_descriptor');
    const faceRequiredErr = document.getElementById('faceRequiredErr');
    const submitRegisterBtn = document.getElementById('submitRegisterBtn');
    const registerForm = document.getElementById('registerForm');
    let modelsLoaded = false;
    let localStream = null;

    // Guard: Block form submission if face not captured
    registerForm.addEventListener('submit', function(e) {
        if (!faceDescriptorInput.value) {
            e.preventDefault();
            faceRequiredErr.style.display = 'block';
            document.getElementById('startCameraBtn').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        faceRequiredErr.style.display = 'none';
    });

    // Load face-api models
    async function loadModels() {
        faceStatus.textContent = "Loading AI Biometric Models...";
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            modelsLoaded = true;
            faceStatus.textContent = "Models integrated. Initiating webcam...";
        } catch (e) {
            faceStatus.textContent = "Failed to load biometric parameters.";
            console.error(e);
        }
    }

    startCameraBtn.addEventListener('click', async () => {
        if (!modelsLoaded) {
            await loadModels();
        }
        
        if (!modelsLoaded) return;
        
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: {} });
            videoFeed.srcObject = localStream;
            videoContainer.classList.remove('d-none');
            startCameraBtn.classList.add('d-none');
            captureFaceBtn.classList.remove('d-none');
            faceStatus.textContent = "Position your face clearly and hold still.";
            faceStatus.className = "small fw-bold text-primary";
        } catch (err) {
            console.error("Webcam error:", err);
            faceStatus.textContent = "Webcam blocked or unavailable. Ensure permissions.";
            faceStatus.className = "small fw-bold text-danger";
        }
    });

    captureFaceBtn.addEventListener('click', async () => {
        faceStatus.textContent = "Generating 128-dimensional mapping...";
        faceStatus.className = "small fw-bold text-warning";
        captureFaceBtn.disabled = true;

        const detections = await faceapi.detectSingleFace(videoFeed).withFaceLandmarks().withFaceDescriptor();

        if (detections) {
            // Generate the JSON array of float32 array
            const descriptorArray = Array.from(detections.descriptor);
            faceDescriptorInput.value = JSON.stringify(descriptorArray);
            
            faceStatus.textContent = "Biometric data verified & cryptographically mapped!";
            faceStatus.className = "small fw-bold text-success";
            
            // Terminate feed after success
            if(localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            videoContainer.classList.add('d-none');
            captureFaceBtn.classList.add('d-none');
        } else {
            faceStatus.textContent = "No face identified. Please improve ambient lighting!";
            faceStatus.className = "small fw-bold text-danger";
            captureFaceBtn.disabled = false;
        }
    });
});
