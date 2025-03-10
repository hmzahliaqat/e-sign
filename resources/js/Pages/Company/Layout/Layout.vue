<template>
    <div>
        <!-- <Navbar :company="company" /> -->
        <RouterView />
    </div>
</template>

<script setup>
import Navbar from '../../Common/Navbar.vue';
import { RouterView } from 'vue-router';
import { useCompanyStore } from '../../../Store/companyStore';
import { useDocumentStore } from '../../../Store/documentStore';
import { defineProps , onMounted } from 'vue';

const companyStore = useCompanyStore();
const documentStore = useDocumentStore();
const props = defineProps(['company' , 'documents']);
const company = props?.company;
onMounted(() => {
  if (props.company) {
    companyStore.setCompany(props.company);
  }

  if(props.documents){
    documentStore.setDocument(props.documents);
  }

});
</script>
