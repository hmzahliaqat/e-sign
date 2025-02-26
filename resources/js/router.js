import { createRouter, createWebHistory } from "vue-router";
import Homepage from "./Pages/Company/Homepage.vue";
import Dashboard from "./Pages/Company/Pages/Dashboard.vue";
import Documents from "./Pages/Company/Pages/Documents.vue";
const routes = [
  {
    path: '/company',
    component: Homepage,
    children: [
        { path: 'dashboard', component: Dashboard },
        { path: 'documents', component: Documents }
    ]
}
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
