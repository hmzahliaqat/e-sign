    <template>

        <div class="pdf-wrapper">
            <!-- Controls positioned above the PDF -->
            <div class="control-panel">
                <button class="btn primary" @click="openSignaturePad">Sign Document</button>
                <button class="btn" @click="savePdf" :disabled="!hasSignature">Save PDF</button>
                <button class="btn" @click="resetPdf">Reset</button>
            </div>

            <!-- PDF container -->
            <div id="pdf-container" class="pdf-container">
                <object v-if="pdfUrl" :data="pdfUrl" type="application/pdf" width="100%" height="1000"
                    class="pdf-viewer">
                    <p>
                        It appears your browser doesn't support embedded PDFs.
                        <a :href="pdfUrl">Click here to download the PDF file.</a>
                    </p>
                </object>
                <div v-else class="pdf-loading">Loading PDF...</div>
            </div>

            <!-- Signature pad modal -->
            <div v-if="showSignaturePad" class="signature-modal">
                <div class="signature-container">
                    <h3>Draw your signature below</h3>
                    <canvas ref="signatureCanvas" class="signature-pad"></canvas>
                    <div class="signature-controls">
                        <button class="btn" @click="clearSignature">Clear</button>
                        <button class="btn primary" @click="addSignature">Apply Signature</button>
                        <button class="btn" @click="cancelSignature">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Signature placement overlay (shows after signature is drawn) -->
            <div v-if="showPlacementOverlay" class="placement-overlay" @click="placeSignature">
                <div class="placement-instructions">
                    Click anywhere on the document to place your signature
                </div>
                <div class="signature-preview"
                    :style="{ left: previewPosition.x + 'px', top: previewPosition.y + 'px' }"
                    @mousedown="startDragging" @mousemove="dragSignature" @mouseup="stopDragging">
                    <img :src="signatureImage" alt="Your signature" />
                </div>
            </div>
        </div>
    </template>

<script setup>
import { ref, onMounted } from 'vue';
import { useDocumentStore } from '../../../../Store/documentStore';
import { useRoute } from 'vue-router';
import { PDFDocument } from 'pdf-lib';

const fileName = ref('');
const documentStore = useDocumentStore();
// Rename "document" to "docData" to avoid conflict with the global document object
const docData = ref(null);
const route = useRoute();
const pdfUrl = ref(null);
const showSignaturePad = ref(false);
const showPlacementOverlay = ref(false);
const signatureCanvas = ref(null);
const signatureImage = ref(null);
const hasSignature = ref(false);
const previewPosition = ref({ x: 100, y: 100 });
const isDragging = ref(false);
const pdfDocument = ref(null);
const originalPdfBytes = ref(null);




// Signature position tracking
const startDragging = (e) => {
    isDragging.value = true;
    e.stopPropagation(); // Prevent triggering the placement click
};

const dragSignature = (e) => {
    if (!isDragging.value) return;
    previewPosition.value = {
        x: e.clientX - 50, // Offset to center the signature on cursor
        y: e.clientY - 25
    };
    e.preventDefault();
};

const stopDragging = () => {
    isDragging.value = false;
};

// Load PDF
const loadPdf = async () => {
    // Use docData instead of document to avoid shadowing the global object
    docData.value = documentStore.document.find(
        (doc) => doc.id == route?.params?.id
    );
    fileName.value = docData.value.pdf_path;
    pdfUrl.value = `/storage/${fileName.value}`;

    try {
        // Fetch the PDF for later manipulation
        const response = await fetch(pdfUrl.value);
        originalPdfBytes.value = await response.arrayBuffer();
        pdfDocument.value = await PDFDocument.load(originalPdfBytes.value);
    } catch (error) {
        console.error('Error loading PDF:', error);
    }
};

// Setup signature pad
const setupSignaturePad = () => {
    const canvas = signatureCanvas.value;
    const ctx = canvas.getContext('2d');
    let isDrawing = false;

    // Set canvas size
    canvas.width = 500;
    canvas.height = 200;

    // Setup drawing styles
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000000';

    // Drawing event handlers
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    // Touch support
    canvas.addEventListener('touchstart', handleTouch(startDrawing));
    canvas.addEventListener('touchmove', handleTouch(draw));
    canvas.addEventListener('touchend', stopDrawing);

    function startDrawing(e) {
        isDrawing = true;
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
    }

    function draw(e) {
        if (!isDrawing) return;
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
    }

    function stopDrawing() {
        isDrawing = false;
    }

    function handleTouch(eventHandler) {
        return (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const offsetX = touch.clientX - rect.left;
            const offsetY = touch.clientY - rect.top;

            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            mouseEvent.offsetX = offsetX;
            mouseEvent.offsetY = offsetY;
            eventHandler(mouseEvent);
        };
    }
};

// Open signature pad
const openSignaturePad = () => {
    showSignaturePad.value = true;
    // Initialize canvas in next tick after it's rendered
    setTimeout(() => {
        setupSignaturePad();
    }, 0);
};

// Clear signature
const clearSignature = () => {
    const canvas = signatureCanvas.value;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
};

// Add signature
const addSignature = () => {
    const canvas = signatureCanvas.value;
    signatureImage.value = canvas.toDataURL('image/png');
    showSignaturePad.value = false;
    showPlacementOverlay.value = true;
    hasSignature.value = true;
};

// Cancel signature
const cancelSignature = () => {
    showSignaturePad.value = false;
    clearSignature();
};

// Place signature on the document
const placeSignature = async (e) => {
    if (isDragging.value) return; // Don't place if currently dragging

    // Calculate position relative to PDF
    const pdfContainer = document.querySelector('.pdf-container');
    const rect = pdfContainer.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Update the UI preview position
    previewPosition.value = { x, y };

    // In a real implementation, you would modify the PDF using pdf-lib here
    console.log(`Signature placed at X: ${x}, Y: ${y}`);
};

// Save PDF with signature
const savePdf = async () => {
    try {
        // Get the PDF container element to calculate coordinate ratios
        const pdfContainer = document.querySelector('.pdf-container');
        if (!pdfContainer) {
            throw new Error("PDF container element not found");
        }

        // Load the original PDF using pdf-lib
        const pdfDoc = await PDFDocument.load(originalPdfBytes.value);
        const pages = pdfDoc.getPages();
        const firstPage = pages[0];
        const { width: pageWidth, height: pageHeight } = firstPage.getSize();

        // Convert the signature (data URL) to an ArrayBuffer
        const signatureResponse = await fetch(signatureImage.value);
        const signatureBytes = await signatureResponse.arrayBuffer();
        const signatureImageEmbed = await pdfDoc.embedPng(signatureBytes);

        // Define the desired signature size in PDF points (adjust as needed)
        const signatureWidthPDF = 200;
        const signatureHeightPDF = 100;

        // Get the dimensions of the PDF container (the area where the PDF is rendered)
        const containerRect = pdfContainer.getBoundingClientRect();
        const containerWidth = containerRect.width;
        const containerHeight = containerRect.height;

        // Calculate the ratios from the preview position relative to the container
        const xRatio = previewPosition.value.x / containerWidth;
        const yRatio = previewPosition.value.y / containerHeight;

        // Convert container-based position to PDF coordinates.
        // Note: PDF coordinate origin is bottom left, so we invert the y-axis.
        const signatureX = xRatio * pageWidth;
        const signatureY = pageHeight - (yRatio * pageHeight) - signatureHeightPDF;

        // Draw the signature on the PDF page
        firstPage.drawImage(signatureImageEmbed, {
            x: signatureX,
            y: signatureY,
            width: signatureWidthPDF,
            height: signatureHeightPDF,
        });

        // Save the modified PDF bytes
        const modifiedPdfBytes = await pdfDoc.save();

        // Create a Blob from the modified PDF and generate a download URL
        const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);


        const pathParts = fileName.value.split('/');

        const filename = pathParts.pop();

        // Trigger a download of the signed PDF
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

        // Update the PDF viewer to show the newly signed PDF
        pdfUrl.value = url;




        // Create a FormData object to send the file to the server
        const formData = new FormData();

        formData.append('file', blob, filename);
        formData.append('filepath', filename); // Send the complete path

        // Use Laravel's CSRF token
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Send to a Laravel route
        const response = await fetch('/replace-pdf', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to save the file');
        }

        const result = await response.json();

        // Update the PDF viewer with a cache buster
        // pdfUrl.value = `${result.file_url}?t=${new Date().getTime()}`;




        alert("PDF saved with your signature!");
    } catch (error) {
        console.error("Error saving PDF:", error);
    }
};

// Reset PDF to original
const resetPdf = () => {
    showPlacementOverlay.value = false;
    hasSignature.value = false;
    signatureImage.value = null;
    // Reload original PDF
    pdfUrl.value = `/storage/${fileName.value}`;
};

// Initialize component
onMounted(() => {
    loadPdf();
});
</script>

<style scoped>
.pdf-wrapper {
    position: relative;
    width: 100%;
}

.control-panel {
    display: flex;
    gap: 10px;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 4px 4px 0 0;
    border: 1px solid #ddd;
    border-bottom: none;
}

.btn {
    padding: 8px 16px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.btn:hover {
    background-color: #e0e0e0;
}

.btn.primary {
    background-color: #4285f4;
    color: white;
    border-color: #3367d6;
}

.btn.primary:hover {
    background-color: #3367d6;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pdf-container {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 0 0 4px 4px;
    overflow: hidden;
    position: relative;
}

.pdf-viewer {
    border: none;
}

.pdf-loading {
    padding: 20px;
    text-align: center;
    color: #666;
}

.signature-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.signature-container {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    width: 550px;
    max-width: 90vw;
}

.signature-pad {
    width: 100%;
    height: 200px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: 15px 0;
    background-color: #fff;
}

.signature-controls {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 15px;
}

.placement-overlay {
    position: absolute;
    top: 70px;
    left: 0;
    right: 70px;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.2);
    z-index: 500;
    cursor: crosshair;
}

.placement-instructions {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background-color: white;
    padding: 10px 20px;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    font-weight: bold;
}

.signature-preview {
    position: absolute;
    cursor: move;
    z-index: 600;
    max-width: 200px;
    max-height: 100px;
    background-color: rgba(255, 255, 255, 0.8);
    border: 1px dashed #aaa;
    padding: 5px;
}

.signature-preview img {
    max-width: 100%;
    max-height: 100%;
}
</style>
