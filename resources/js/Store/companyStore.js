import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useCompanyStore = defineStore('employee', () => {
    const company = ref([]);

    const setCompany = (data) => {
        company.value = data;
    };

    const addCompany = (company) => {
        company.value.push(company);
    };

    return { company, setCompany, addCompany };
});
