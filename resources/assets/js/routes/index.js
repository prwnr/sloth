import Vue from 'vue';
import VueRouter from 'vue-router';

import RolesIndex from '../components/cruds/Roles/Index.vue';
import RolesCreate from '../components/cruds/Roles/Create.vue';
import RolesShow from '../components/cruds/Roles/Show.vue';
import RolesEdit from '../components/cruds/Roles/Edit.vue';

import MembersIndex from '../components/cruds/Members/Index.vue';
import MembersCreate from '../components/cruds/Members/Create.vue';
import MembersShow from '../components/cruds/Members/Show.vue';
import MembersEdit from '../components/cruds/Members/Edit.vue';

import ProjectsIndex from '../components/cruds/Projects/Index.vue';
import ProjectsCreate from '../components/cruds/Projects/Create.vue';
import ProjectsShow from '../components/cruds/Projects/Show.vue';
import ProjectsEdit from '../components/cruds/Projects/Edit.vue';

import ClientsIndex from '../components/cruds/Clients/Index.vue';
import ClientsCreate from '../components/cruds/Clients/Create.vue';
import ClientsShow from '../components/cruds/Clients/Show.vue';
import ClientsEdit from '../components/cruds/Clients/Edit.vue';

import Dashboard from '../components/views/Dashboard.vue';
import Tracker from '../components/views/Tracker.vue';
import Profile from '../components/views/Profile.vue';

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
    { path: '/profile', component: Profile, name: 'profile' }
];

export default new VueRouter({
    mode: 'history',
    routes,
    linkActiveClass: 'active',
    linkExactActiveClass: 'active'
});
