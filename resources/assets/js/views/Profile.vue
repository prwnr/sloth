<template>
    <div class="content">
        <section class="content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profile</h1>
                </div>
            </div>
        </section>
        <loading v-if="loading"></loading>
        <section class="content mb-6">
            <div class="row" v-if="!loading">
                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-user">
                            <div class="text-center">
                                <i class="fa fa-user-circle-o"></i>
                            </div>

                            <h3 class="user-username text-center">{{ user.data.fullname }}</h3>
                            <p class="text-muted text-center">{{ roles }}</p>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Team</b> <a class="float-right">{{ user.team.name }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Projects</b> <a class="float-right">{{ user.projects.length }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
                <div class="col-md-9 mb-3">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li>
                                <li class="nav-item"><a class="nav-link" href="#reports" data-toggle="tab">My reports</a></li>
                                <li v-if="$user.hasRole('admin')" class="nav-item">
                                    <a class="nav-link" href="#team" data-toggle="tab">My team</a></li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="activity">
                                    <activity></activity>
                                </div>
                                <div class="tab-pane" id="reports">
                                    <reports></reports>
                                </div>
                                <div class="tab-pane" id="settings">
                                    <settings @userUpdated="updateUser" :user="user.data"></settings>
                                </div>
                                <div v-if="$user.hasRole('admin')" class="tab-pane" id="team">
                                    <team @teamUpdated="updateTeam" :team="user.team"></team>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import Team from './../components/Profile/Team.vue';
    import Settings from './../components/Profile/Settings.vue';
    import Reports from './../components/Profile/Reports.vue';
    import Activity from './../components/Profile/Activity.vue';
    import User from "../models/User";

    export default {
        components: {
            Team,
            Settings,
            Reports,
            Activity
        },

        data() {
            return {
                loading: false,
                user: {
                    data: {},
                    roles: [],
                    permissions: [],
                    team: {},
                    projects: []
                }
            }
        },

        created() {
            this.fetchUser();
        },

        computed: {
            roles: function () {
                let roles = this.user.roles.map(item => item.display_name);
                return roles.join(', ');
            }
        },

        methods: {
            fetchUser() {
                this.loading = true;
                axios.get('/api/users/active').then(response => {
                    this.user = new User(response.data);
                }).catch(error => {
                    reject(error.response.data);
                }).finally(() => {
                    this.loading = false;
                });
            },
            /**
             * Update user first and last name if he changed his data
             * @param user
             */
            updateUser(user) {
                let fullname = user.firstname + ' ' + user.lastname;
                if (this.user.data.fullname != fullname) {
                    this.user.data.fullname = fullname;
                    this.user.data.firstname = user.firstname;
                    this.user.data.lastname = user.lastname;
                }
            },

            /**
             * Update user team name
             * @param team
             */
            updateTeam(team) {
                if (team.name != this.user.team.name) {
                    this.user.team.name = team.name;
                }
            }
        }
    }
</script>

<style scoped>
    .fa-user-circle-o {
        font-size: 80px;
    }

    .mb-6 {
        margin-bottom: 4rem !important;
    }

    .row div {
        margin-bottom: 1px;
    }
</style>