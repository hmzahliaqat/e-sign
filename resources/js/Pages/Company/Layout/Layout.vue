<template>
    <div>
        <Navbar :company="company" />
        <RouterView />
    </div>
</template>

<script setup>
import Navbar from '../../Common/Navbar.vue';
import { RouterView } from 'vue-router';
import { useCompanyStore } from '../../../Store/companyStore';
import { defineProps , onMounted } from 'vue';

const companyStore = useCompanyStore();

const props = defineProps(['company']);
const company = props?.company;
onMounted(() => {
  if (props.company) {
    companyStore.setCompany(props.company);
  }
});
</script>
