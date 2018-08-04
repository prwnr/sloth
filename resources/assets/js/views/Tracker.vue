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
                                data-target="#newLog">Add time <i class="fa fa-fw fa-plus"></i></button>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="card mb-3">
                <card-header :minimizable="false">Your logs from {{ viewedDay }}</card-header>
                <div class="card-body col-lg-12">
                    <span v-if="timeLogs.length == 0">You haven't worked yet this day. Slothfully</span>
                    <time-log v-for="time in timeLogs" :key="time.id" :time="time" :projects="projects" @logDeleted="deleteLog" @editTime="handleEditDialog"></time-log>
                </div>
            </div>

            <div class="modal fade" id="newLog" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content card-primary card-outline">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <new-log :projects="projects"></new-log>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editRow" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content card-primary card-outline">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeEditDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <edit-log v-if="editedTime" :time="editedTime" :projects="projects" @logUpdated="updateLog"></edit-log>
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
    import NewLog from './Tracker/NewLog.vue';
    import EditLog from './Tracker/EditLog.vue';

    export default {
        components: {
            EditLog,
            TimeLog,
            NewLog
        },

        data() {
            return {
                viewedDay: '',
                timeLogs: [],
                projects: [],
                editedTime: null
            }
        },

        created() {
            this.fetchData();
        },

        methods: {
            /**
             * Delete log by ID
             */
            deleteLog(logId) {
                axios.delete('/api/time/' + logId).then(response => {
                    this.timeLogs = this.timeLogs.filter(item => item.id !== logId);

                    this.$awn.success('Log succesfully deleted.');
                }).catch(error => {
                    this.$awn.alert(error.message);
                })
            },

            /**
             * Update log based on given data
             */
            updateLog(data) {
                let index = this.timeLogs.findIndex(item => item.id === data.id);

                let project = this.projects.find(item => item.id === data.project_id);
                this.timeLogs[index].project = project;
                this.timeLogs[index].task = project.tasks.find(item => item.id === data.task_id);
                this.timeLogs[index].description = data.description;
            },

            /**
             * Handles displaying edit dialog modal for single log
             */
            handleEditDialog(timeLog) {
                this.editedTime = timeLog;
                let self = this;
                $('#editRow').on('hidden.bs.modal', function (e) {
                    self.editedTime = null;
                })
            },

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
                axios.get('/api/users/' + this.$user.data.id + '/logs', {
                    params: {
                        date: null
                    }
                }).then(response => {
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