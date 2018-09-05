import Vue from 'vue';
import VueRouter from 'vue-router';

import RolesIndex from '../views/Roles/Index.vue';
import RolesCreate from '../views/Roles/Create.vue';
import RolesShow from '../views/Roles/Show.vue';
import RolesEdit from '../views/Roles/Edit.vue';

import MembersIndex from '../views/Members/Index.vue';
import MembersCreate from '../views/Members/Create.vue';
import MembersShow from '../views/Members/Show.vue';
import MembersEdit from '../views/Members/Edit.vue';

import ProjectsIndex from '../views/Projects/Index.vue';
import ProjectsCreate from '../views/Projects/Create.vue';
import ProjectsShow from '../views/Projects/Show.vue';
import ProjectsEdit from '../views/Projects/Edit.vue';

import ClientsIndex from '../views/Clients/Index.vue';
import ClientsCreate from '../views/Clients/Create.vue';
import ClientsShow from '../views/Clients/Show.vue';
import ClientsEdit from '../views/Clients/Edit.vue';

import Dashboard from '../views/Dashboard.vue';
import Tracker from '../views/Tracker.vue';
import Profile from '../views/Profile.vue';
import Reports from '../views/Reports.vue';

Vue.use(VueRouter);

const routes = [
    { path: '/', component: Dashboard, name: 'dashboard' },
    { path: '/time', component: Tracker, name: 'tracker' },

    //Roles routes
    { path: '/roles', component: RolesIndex, name: 'roles.index' },
    { path: '/roles/create', component: RolesCreate, name: 'roles.create' },
    { path: '/roles/:id', component: RolesShow, name: 'roles.show' },
    { path: '/roles/:id/edit', component: RolesEdit, name: 'roles.edit' },

    //Members routes
    { path: '/members', component: MembersIndex, name: 'members.index' },
    { path: '/members/create', component: MembersCreate, name: 'members.create' },
    { path: '/members/:id', component: MembersShow, name: 'members.show' },
    { path: '/members/:id/edit', component: MembersEdit, name: 'members.edit' },

    //Projects routes
    { path: '/projects', component: ProjectsIndex, name: 'projects.index' },
    { path: '/projects/create', component: ProjectsCreate, name: 'projects.create' },
    { path: '/projects/:id', component: ProjectsShow, name: 'projects.show' },
    { path: '/projects/:id/edit', component: ProjectsEdit, name: 'projects.edit' },

    //Clients routes
    { path: '/clients', component: ClientsIndex, name: 'clients.index' },
    { path: '/clients/create', component: ClientsCreate, props: { isModal: false }, name: 'clients.create' },
    { path: '/clients/:id', component: ClientsShow, name: 'clients.show' },
    { path: '/clients/:id/edit', component: ClientsEdit, name: 'clients.edit' },

    //Profile route
    { path: '/profile', component: Profile, name: 'profile' },

    //Reports routes
    { path: '/reports', component: Reports, name: 'reports' },
];

export default new VueRouter({
    mode: 'history',
    routes,
    linkActiveClass: 'active',
    linkExactActiveClass: 'active'
});
