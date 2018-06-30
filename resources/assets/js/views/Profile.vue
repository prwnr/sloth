<template>
    <div class="content">
        <section class="content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profile</h1>
                </div>
            </div>
        </section>
        <section class="content mb-6">
            <div class="row">
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

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">About Me</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fa fa-book mr-1"></i> Education</strong>
                            <p class="text-muted">
                                B.S. in Computer Science from the University of Tennessee at Knoxville
                            </p>
                            <hr>
                            <strong><i class="fa fa-map-marker mr-1"></i> Location</strong>
                            <p class="text-muted">Malibu, California</p>
                            <hr>
                            <strong><i class="fa fa-pencil mr-1"></i> Skills</strong>
                            <p class="text-muted">
                                <span class="tag tag-danger">UI Design</span>
                                <span class="tag tag-success">Coding</span>
                                <span class="tag tag-info">Javascript</span>
                                <span class="tag tag-warning">PHP</span>
                                <span class="tag tag-primary">Node.js</span>
                            </p>
                            <hr>
                            <strong><i class="fa fa-file-text-o mr-1"></i> Notes</strong>
                            <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam
                                fermentum enim neque.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#settings" data-toggle="tab">Settings</a>
                                </li>
                                <li v-if="$user.hasRole('admin')" class="nav-item">
                                    <a class="nav-link" href="#team" data-toggle="tab">My team</a></li>
                            </ul>
                        </div>
                        <div class="card-body">

                            <div class="tab-content">
                                <div class="tab-pane active" id="activity">
                                    <activity></activity>
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
    import Activity from './../components/Profile/Activity.vue';

    export default {
        components: {
            Team,
            Settings,
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
            this.user = this.$user;
        },

        computed: {
            roles: function () {
                let roles = this.user.roles.map(item => item.display_name);
                return roles.join(', ');
            }
        },

        methods: {
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
</style>