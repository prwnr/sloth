<template>
    <div class="sidebar">
        <div class="user-panel pl-3 mt-3 pb-3 mb-3 d-flex">
            <div class="user-panel-icon">
                <i class="fa fa-user-circle"></i>
            </div>
            <div class="info">
                <router-link :to="{ name: 'profile' }" :title="$user.get('fullname')" class="d-block">
                    {{ $user.get('fullname') }}
                </router-link>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <router-link class="nav-link" :to="{ path: '/' }" tag="a" exact>
                        <i class="fa nav-icon fa-dashboard"></i>
                        <p>Dashboard</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link v-if="$user.can('track_time')" class="nav-link" :to="{ name: 'tracker' }" tag="a">
                        <i class="fa nav-icon fa-clock-o"></i>
                        <p>Time</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link v-if="$user.can('manage_team')" class="nav-link" :to="{ name: 'members.index' }" tag="a">
                        <i class="fa nav-icon fa-users"></i>
                        <p>Team</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link v-if="$user.can('manage_clients')" class="nav-link" :to="{ name: 'clients.index' }" tag="a">
                        <i class="fa nav-icon fa-user"></i>
                        <p>Clients</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link v-if="$user.can('manage_projects')" class="nav-link" :to="{ name: 'projects.index' }" tag="a">
                        <i class="fa nav-icon fa-briefcase"></i>
                        <p>Projects</p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link v-if="$user.can('manage_roles')" class="nav-link" :to="{ name: 'roles.index' }" tag="a">
                        <i class="fa nav-icon fa-universal-access"></i>
                        <p>Roles and permissions</p>
                    </router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    export default {
    }
</script>

<style scoped>

</style>