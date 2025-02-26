import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useCompanyStore = defineStore('employee', () => {
    const company = ref([]); // Reactive state

    const setCompany = (data) => {
        company.value = data;
    };

    const addCompany = (employee) => {
        company.value.push(employee);
    };

    return { company, setCompany, addCompany };
});
