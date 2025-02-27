<template>
    <div>
        <div class="card p-2">
            <Toolbar class="mb-2">
                <template #start>
                    <Button label="Delete" icon="pi pi-trash" severity="danger" outlined @click="confirmDeleteSelected"
                        :disabled="!selectedDocuments || !selectedDocuments.length" />
                </template>

                <template #end>
                    <FileUpload @uploader="uploadDocument" mode="basic" accept=".pdf,.doc,.docx,.txt"
                        :maxFileSize="1000000" label="Import" customUpload chooseLabel="Import" class="mr-2" auto
                        :chooseButtonProps="{ severity: 'secondary' }" />
                        <small v-if="errorMessage" class="p-error">{{ errorMessage }}</small>
                </template>

            </Toolbar>
            <ProgressBar v-if="uploading"  :value="uploadProgress" class="mt-3 mb-3"></ProgressBar>

            <DataTable ref="dt" v-model:selection="selectedDocuments" :value="documents" dataKey="id" :paginator="true"
                :rows="8" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[8, 20, 45]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} documents">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Documents</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="Document ID" sortable style="min-width: 12rem"></Column>
                <Column field="title" header="Title" sortable style="min-width: 16rem"></Column>
                <Column field="file_type" header="File Type" sortable style="min-width: 10rem"></Column>
                <Column field="created_at" header="Created At" sortable style="min-width: 10rem"></Column>
                <Column field="page_count" header="Pages" sortable style="min-width: 8rem"></Column>

                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-eye" class="mr-2" outlined rounded severity="info"
                        @click="viewDocument(slotProps.data.id)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteDocument(slotProps.data)" />

                    </template>
                </Column>
            </DataTable>
        </div>


        <Dialog v-model:visible="deleteDocumentDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="document">Are you sure you want to delete <b>{{ document.title }}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteDocumentDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteDocument" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteDocumentsDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="document">Are you sure you want to delete the selected documents?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteDocumentsDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedDocuments" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// import { FilterMatchMode } from 'primevue/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import RadioButton from 'primevue/radiobutton';
import InputNumber from 'primevue/inputnumber';
import Toolbar from 'primevue/toolbar';
import FileUpload from 'primevue/fileupload';
import Select from 'primevue/select';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { useDocumentStore } from '../../../Store/documentStore';
import ProgressBar from 'primevue/progressbar';
import {useToast} from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import { useRouter } from 'vue-router';

const uploading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref('');
const dt = ref();
const documentStore = useDocumentStore();
const documents = ref([]);
const $toast = useToast();
const router = useRouter();
const documentDialog = ref(false);
const deleteDocumentDialog = ref(false);
const deleteDocumentsDialog = ref(false);
const document = ref({});
const selectedDocuments = ref();
const filters = ref({
    'global': { value: null },
});
const submitted = ref(false);
const statuses = ref([
    { label: 'ACTIVE', value: 'ACTIVE' },
    { label: 'ARCHIVED', value: 'ARCHIVED' },
    { label: 'DRAFT', value: 'DRAFT' }
]);


const uploadDocument = async(event) => {

    const file = event.files[0];

    if (!file) {
        errorMessage.value = 'No file selected';
        return;
    }
    // Reset states
    uploading.value = true;
    uploadProgress.value = 0;
    errorMessage.value = '';

    const formData = new FormData();
    formData.append('file', file);
    formData.append('title', file.name);

    try {
        const response = await axios.post('/save/document', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: (progressEvent) => {
            uploadProgress.value = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            );
          }
        });


        $toast.success('Document uploaded!',{
            position:'top-right',
        });
        // Redirect to document editor
        window.location.href = `/company/document/${response.data.id}/preview`;
      } catch (error) {
        console.error('Upload failed:', error);
        errorMessage.value = error.response?.data?.message || 'Upload failed. Please try again.';

        $toast.error('Upload failed!',{
            position:'top-right',
        });
      } finally {
        uploading.value = false;
      }
}

const viewDocument = (id) =>{
    router.push(`/document/${id}/preview`);
}

const hideDialog = () => {
    documentDialog.value = false;
    submitted.value = false;
};

const saveDocument = () => {
    submitted.value = true;

    if (document?.value.title?.trim()) {
        if (document.value.id) {
            document.value.status = document.value.status.value ? document.value.status.value : document.value.status;
            documents.value[findIndexById(document.value.id)] = document.value;
        }
        else {
            document.value.id = 'DOC' + Math.floor(1000 + Math.random() * 9000);
            document.value.created_at = new Date().toISOString().slice(0, 10);
            document.value.status = document.value.status ? document.value.status.value : 'ACTIVE';
            document.value.type = 'PDF'; // Default type
            documents.value.push(document.value);
        }

        documentDialog.value = false;
        document.value = {};
    }
};


const confirmDeleteDocument = (doc) => {
    document.value = doc;
    deleteDocumentDialog.value = true;
};

const deleteDocument = () => {
    documents.value = documents.value.filter(val => val.id !== document.value.id);
    deleteDocumentDialog.value = false;
    document.value = {};
};

const findIndexById = (id) => {
    let index = -1;
    for (let i = 0; i < documents.value.length; i++) {
        if (documents.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};


const confirmDeleteSelected = () => {
    deleteDocumentsDialog.value = true;
};

const deleteSelectedDocuments = () => {
    documents.value = documents.value.filter(val => !selectedDocuments.value.includes(val));
    deleteDocumentsDialog.value = false;
    selectedDocuments.value = null;
};

onMounted(() => {
    documents.value = documentStore?.document;
});
</script>
