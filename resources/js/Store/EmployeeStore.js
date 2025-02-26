import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useEmployeeStore = defineStore('employee', () => {
    const employees = ref([]); // Reactive state

    const setEmployees = (data) => {
        employees.value = data;
    };

    const addEmployee = (employee) => {
        employees.value.push(employee);
    };

    return { employees, setEmployees, addEmployee };
});
