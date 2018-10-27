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
                                    <b>Teams: </b>
                                    <a class="float-right">{{ teamsText }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Projects:</b> <a class="float-right">{{ user.projects.length }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
                <div class="col-md-9 mb-3">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <!--<li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab" @click="toggleTab">Activity</a></li>-->
                                <li class="nav-item"><a class="nav-link active" href="#reports" data-toggle="tab" @click="toggleTab">My reports</a></li>
                                <li v-if="$user.hasRole('admin')" class="nav-item">
                                    <a class="nav-link" href="#team" data-toggle="tab" @click="toggleTab">My team</a></li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab" @click="toggleTab">Settings</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!--<div class="tab-pane active" id="activity">-->
                                    <!--<activity v-if="activeTab == 'activity'"></activity>-->
                                <!--</div>-->
                                <div class="tab-pane active" id="reports">
                                    <reports v-if="activeTab == 'reports'"></reports>
                                </div>
                                <div class="tab-pane" id="settings">
                                    <settings v-if="activeTab == 'settings'" @userUpdated="updateUser" :user="user.data"></settings>
                                </div>
                                <div v-if="$user.hasRole('admin')" class="tab-pane" id="team">
                                    <team v-if="activeTab == 'team'" @teamUpdated="updateTeam" :team="user.team"></team>
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
                activeTab: 'reports',
                teams: [],
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
            },
            teamsText: function () {
                let teams = this.teams.map(item => item.name);
                return teams.join(', ');
            }
        },

        methods: {
            fetchUser() {
                this.loading = true;
                axios.get(`/api/users/${this.$user.get('id')}`).then(response => {
                    this.user = new User(response.data);
                    this.teams = response.data.teams;
                }).catch(error => {
                    this.$awn.alert(error.message);
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
            },

            /**
             * Sets activeTab to currently toggled tab
             */
            toggleTab(e) {
                let name = e.currentTarget.getAttribute('href');
                name = name.substr(1);

                this.activeTab = name;
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