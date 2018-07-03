<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Time tracker</h1>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success btn-block" type="button" data-toggle="modal"
                                data-target="#newRow">Add time <i class="fa fa-fw fa-plus"></i></button>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="card mb-3">
                <card-header :minimizable="false">Your logs from {{ viewedDay }}</card-header>
                <div class="card-body col-lg-12">
                    <span v-if="timeLogs.length == 0">You haven't worked yet this day. Slothfully</span>
                    <time-log v-for="(time, index) in timeLogs" :time="time" :key="index"></time-log>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="newRow" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <new-time :projects="projects"></new-time>
                        </div>
                    </div>
                </div>
            </div>
            <back-button></back-button>
        </section>
    </div>
</template>

<script>
    import TimeLog from './Tracker/TimeLog.vue';
    import NewTime from './Tracker/NewTime.vue';

    export default {
        components: {
            TimeLog,
            NewTime
        },

        data() {
            return {
                viewedDay: '',
                timeLogs: [],
                projects: []
            }
        },

        created() {
            this.fetchData();
        },

        methods: {
            /**
             * Fetch tracker data
             */
            fetchData() {
                if (!this.$user.data.member) {
                    axios.get('/api/projects').then(response => {
                        this.projects = response.data.data;
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    });

                    this.fetchTimeLogs();
                    return;
                }

                axios.get('/api/members/' + this.$user.data.member.id + '/projects').then(response => {
                    this.projects = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
                this.fetchTimeLogs();
            },

            /**
             * Fetch logs for given user
             */
            fetchTimeLogs() {
                axios.get('/api/users/' + this.$user.data.id + '/times').then(response => {
                    this.timeLogs = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }

    }
</script>

<style scoped>
    .modal-dialog {
        margin-top: 10% !important;
    }
</style>