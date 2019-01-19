<template>
    <div class="sidebar">
        <team-switch @change="updateUser"></team-switch>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <router-link
                            :to="{ path: '/' }"
                            class="nav-link"
                            exact
                            tag="a">
                        <i class="fa nav-icon fa-dashboard"></i>
                        <p>Dashboard</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'tracker' }"
                            class="nav-link"
                            tag="a"
                            v-if="authUser.can('track_time')">
                        <i class="fa nav-icon fa-clock-o"></i>
                        <p>Time</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'reports' }"
                            class="nav-link"
                            tag="a"
                            v-if="authUser.can('view_reports')">
                        <i class="fa nav-icon fa-bar-chart"></i>
                        <p>Reports</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'todo' }"
                            class="nav-link"
                            tag="a">
                        <i class="fa nav-icon fa-list-ul"></i>
                        <p>Todo list</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'members.index' }"
                            class="nav-link"
                            tag="a"
                            v-if="authUser.can('manage_team')">
                        <i class="fa nav-icon fa-users"></i>
                        <p>Team</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'clients.index' }"
                            class="nav-link"
                            tag="a"
                            v-if="authUser.can('manage_clients')">
                        <i class="fa nav-icon fa-user"></i>
                        <p>Clients</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'projects.index' }"
                            class="nav-link"
                            tag="a"
                            v-if="authUser.can('manage_projects')">
                        <i class="fa nav-icon fa-briefcase"></i>
                        <p>Projects</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link
                            :to="{ name: 'roles.index' }"
                            class="nav-link"
                            tag="a"
                            v-if="authUser.can('manage_roles')">
                        <i class="fa nav-icon fa-universal-access"></i>
                        <p>Roles and permissions</p>
                    </router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import TeamSwitch from './TeamSwitch';
    import User from '../models/User.js';
    import {mapGetters, mapMutations} from 'vuex';

    export default {
        name: 'Sidebar',
        components: {
            TeamSwitch
        },

        computed: {
            ...mapGetters(['authUser'])
        },

        methods: {
            ...mapMutations(['setAuthUser']),
            updateUser(user) {
                this.setAuthUser(new User(user))
                EventHub.fire('user_change', true);
                EventHub.fire('dashboard_change', true);
            }
        }
    }
</script>

<style scoped>

</style>