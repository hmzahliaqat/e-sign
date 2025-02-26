<template>
    <div>
        <div class="card p-2">
            <Toolbar class="mb-2">
                <template #start>
                    <Button label="New" icon="pi pi-plus" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="danger" outlined @click="confirmDeleteSelected" :disabled="!selectedEmployees || !selectedEmployees.length" />
                </template>

                <template #end>
                    <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" customUpload chooseLabel="Import" class="mr-2" auto :chooseButtonProps="{ severity: 'secondary' }" />
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable
                ref="dt"
                v-model:selection="selectedEmployees"
                :value="employees"
                dataKey="id"
                :paginator="true"
                :rows="8"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[8, 20, 45]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} employees"
            >
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Employees</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="employeeId" header="Employee ID" sortable style="min-width: 12rem"></Column>
                <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>


                <Column field="email" header="Email" sortable style="min-width: 10rem"></Column>

                <Column field="created_at" header="Created At" sortable style="min-width: 10rem"></Column>


                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editEmployee(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteEmployee(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="employeeDialog" :style="{ width: '450px' }" header="Employee Details" :modal="true">
            <div class="flex flex-col gap-6">
                <!-- <img src="/api/placeholder/200/200" alt="Employee" class="block m-auto pb-4" /> -->
                <div>
                    <label for="name" class="block font-bold mb-3">Name</label>
                    <InputText id="name" v-model.trim="employee.name" required="true" autofocus :invalid="submitted && !employee.name" fluid />
                    <small v-if="submitted && !employee.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3">Job Description</label>
                    <Textarea id="description" v-model="employee.description" required="true" rows="3" cols="20" fluid />
                </div>
                <div>
                    <label for="status" class="block font-bold mb-3">Employment Status</label>
                    <Select id="status" v-model="employee.status" :options="statuses" optionLabel="label" placeholder="Select a Status" fluid></Select>
                </div>

                <div>
                    <span class="block font-bold mb-4">Department</span>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="department1" v-model="employee.department" name="department" value="IT" />
                            <label for="department1">IT</label>
                        </div>
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="department2" v-model="employee.department" name="department" value="HR" />
                            <label for="department2">HR</label>
                        </div>
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="department3" v-model="employee.department" name="department" value="Finance" />
                            <label for="department3">Finance</label>
                        </div>
                        <div class="flex items-center gap-2 col-span-6">
                            <RadioButton id="department4" v-model="employee.department" name="department" value="Marketing" />
                            <label for="department4">Marketing</label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-6">
                        <label for="salary" class="block font-bold mb-3">Salary</label>
                        <InputNumber id="salary" v-model="employee.salary" mode="currency" currency="USD" locale="en-US" fluid />
                    </div>
                    <div class="col-span-6">
                        <label for="performance" class="block font-bold mb-3">Performance (1-5)</label>
                        <InputNumber id="performance" v-model="employee.performance" :min="1" :max="5" integeronly fluid />
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveEmployee" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteEmployeeDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="employee"
                    >Are you sure you want to delete <b>{{ employee.name }}</b
                    >?</span
                >
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteEmployeeDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteEmployee" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteEmployeesDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="employee">Are you sure you want to delete the selected employees?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteEmployeesDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedEmployees" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref } from 'vue';
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
import Rating from 'primevue/rating';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';

const dt = ref();
const employees = ref([
    {
        id: '1001',
        employeeId: 'EMP001',
        name: 'John Smith',
        description: 'Senior Software Developer with 8 years experience in full-stack development.',
        salary: 85000,
        email: 'hmzah@gmail.com',
        created_at:'27 Feb 2022',
    },
]);

const employeeDialog = ref(false);
const deleteEmployeeDialog = ref(false);
const deleteEmployeesDialog = ref(false);
const employee = ref({});
const selectedEmployees = ref();
const filters = ref({
    'global': {value: null},
});
const submitted = ref(false);
const statuses = ref([
    {label: 'ACTIVE', value: 'ACTIVE'},
    {label: 'LEAVE', value: 'LEAVE'},
    {label: 'INACTIVE', value: 'INACTIVE'}
]);


const openNew = () => {
    employee.value = {};
    submitted.value = false;
    employeeDialog.value = true;
};

const hideDialog = () => {
    employeeDialog.value = false;
    submitted.value = false;
};

const saveEmployee = () => {
    submitted.value = true;

    if (employee?.value.name?.trim()) {
        if (employee.value.id) {
            employee.value.status = employee.value.status.value ? employee.value.status.value : employee.value.status;
            employees.value[findIndexById(employee.value.id)] = employee.value;
        }
        else {
            employee.value.id = createId();
            employee.value.employeeId = 'EMP' + Math.floor(1000 + Math.random() * 9000);
            employee.value.status = employee.value.status ? employee.value.status.value : 'ACTIVE';
            employees.value.push(employee.value);
        }

        employeeDialog.value = false;
        employee.value = {};
    }
};

const editEmployee = (emp) => {
    employee.value = {...emp};
    employeeDialog.value = true;
};

const confirmDeleteEmployee = (emp) => {
    employee.value = emp;
    deleteEmployeeDialog.value = true;
};

const deleteEmployee = () => {
    employees.value = employees.value.filter(val => val.id !== employee.value.id);
    deleteEmployeeDialog.value = false;
    employee.value = {};
};

const findIndexById = (id) => {
    let index = -1;
    for (let i = 0; i < employees.value.length; i++) {
        if (employees.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};

const createId = () => {
    let id = '';
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ( var i = 0; i < 5; i++ ) {
        id += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return id;
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const confirmDeleteSelected = () => {
    deleteEmployeesDialog.value = true;
};

const deleteSelectedEmployees = () => {
    employees.value = employees.value.filter(val => !selectedEmployees.value.includes(val));
    deleteEmployeesDialog.value = false;
    selectedEmployees.value = null;
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'ACTIVE':
            return 'success';

        case 'LEAVE':
            return 'warn';

        case 'INACTIVE':
            return 'danger';

        default:
            return null;
    }
};
</script>
