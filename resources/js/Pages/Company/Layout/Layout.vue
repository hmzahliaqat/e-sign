<template>
    <div>
        <Navbar />
        <RouterView />
    </div>
</template>

<script setup>
import Navbar from '../../Common/Navbar.vue';
import { RouterView } from 'vue-router';
</script>
