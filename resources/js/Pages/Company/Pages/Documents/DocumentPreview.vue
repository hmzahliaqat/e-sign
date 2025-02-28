<template>
    <div class="pdf-container">
        <object v-if="pdfUrl" :data="pdfUrl" type="application/pdf" width="100%" height="800" class="pdf-viewer">
            <p>
                It appears your browser doesn't support embedded PDFs.
                <a :href="pdfUrl">Click here to download the PDF file.</a>
            </p>
        </object>
        <div v-else class="pdf-loading">Loading PDF...</div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useDocumentStore } from '../../../../Store/documentStore'
import { useRoute } from 'vue-router';
const fileName = ref('');
const documentStore = useDocumentStore();
const document = ref(null);
const route = useRoute();

const pdfUrl = ref(null)

// Load PDF
const loadPdf = () => {
    document.value = documentStore.document.find(doc => doc.id == route?.params?.id);
    fileName.value = document.value.pdf_path;


    pdfUrl.value = `/storage/${fileName.value}`
    // pdfUrl.value = `/api/documents/view/${fileName}`
}

onMounted(() => {
    loadPdf()
})
</script>

<style scoped>
.pdf-container {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.pdf-viewer {
    border: none;
}

.pdf-loading {
    padding: 20px;
    text-align: center;
    color: #666;
}
</style>
