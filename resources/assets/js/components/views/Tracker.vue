<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Time tracker</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-lg-12">
                    <button class="btn btn-success" type="button" data-toggle="modal" data-target="#newRow">Add time <i class="fa fa-fw fa-plus"></i></button>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
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
            <back-buttton></back-buttton>
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