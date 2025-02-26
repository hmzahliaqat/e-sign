<template>
    <div>
        <Navbar :company="company" />
        <RouterView />
    </div>
</template>

<script setup>
import Navbar from '../../Common/Navbar.vue';
import { RouterView } from 'vue-router';

import { defineProps } from 'vue';

const props = defineProps(['company']); // Get company from attrs
const company = props?.company;


</script>
