<template>
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" title="My profile" class="d-block">Alexander Pierce</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <router-link class="nav-item" :to="{ path: '/' }" tag="li" exact>
                    <a class="nav-link">
                        <i class="fa nav-icon fa-dashboard"></i>
                        <p>Dashboard</p>
                    </a>
                </router-link>

                <router-link v-if="$user.can('track_time')" class="nav-item" :to="{ name: 'tracker' }" tag="li">
                    <a class="nav-link">
                        <i class="fa nav-icon fa-clock-o"></i>
                        <p>Time</p>
                    </a>
                </router-link>

                <router-link v-if="$user.can('manage_team')" class="nav-item" :to="{ name: 'members.index' }" tag="li">
                    <a class="nav-link">
                        <i class="fa nav-icon fa-users"></i>
                        <p>Team</p>
                    </a>
                </router-link>

                <router-link v-if="$user.can('manage_clients')" class="nav-item" :to="{ path: '/clients' }" tag="li">
                    <a class="nav-link">
                        <i class="fa nav-icon fa-user"></i>
                        <p>Clients</p>
                    </a>
                </router-link>

                <router-link v-if="$user.can('manage_projects')" class="nav-item" :to="{ path: '/projects' }" tag="li">
                    <a class="nav-link">
                        <i class="fa nav-icon fa-briefcase"></i>
                        <p>Projects</p>
                    </a>
                </router-link>

                <router-link v-if="$user.can('manage_roles')" class="nav-item" :to="{ name: 'roles.index' }" tag="li">
                    <a class="nav-link">
                        <i class="fa nav-icon fa-universal-access"></i>
                        <p>Roles and permissions</p>
                    </a>
                </router-link>
            </ul>
        </nav>
    </div>
</template>

<script>
    export default {}
</script>

<style scoped>

</style>