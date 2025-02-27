import { defineStore } from 'pinia';
import { ref , watch } from 'vue';

export const useCompanyStore = defineStore('employee', () => {
    const company = ref(JSON.parse(localStorage.getItem('company')) || []);

    const setCompany = (data) => {
        company.value = data;
    };

    const addCompany = (company) => {
        company.value.push(company);
    };

    watch(company, (newVal) => {
        localStorage.setItem('company', JSON.stringify(newVal));
    }, { deep: true });

    return { company, setCompany, addCompany };
});
