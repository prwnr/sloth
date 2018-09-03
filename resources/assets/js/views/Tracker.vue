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
                <div class="card-header">
                    <h3 class="d-inline">Logs from: {{ currentDayText }}</h3>
                    <div class="card-tools">
                        <div class="d-inline ">
                            <div class="btn-group">
                                <button class="btn btn-default" @click="previous"><i class="fa fa-angle-left"></i></button>
                                <button class="btn btn-default" @click="reset">today</button>
                                <button class="btn btn-default" @click="next"><i class="fa fa-angle-right"></i></button>
                            </div>
                        </div>
                        <div class="d-inline ml-2 calendar-icon">
                            <date-picker
                                    @selected="calendarChange"
                                    wrapper-class="btn btn-default"
                                    input-class="hide"
                                    calendar-class="calendar-content"
                                    :calendar-button="true"
                                    calendar-button-icon="fa fa-calendar"
                                    format="yyyy-MM-dd"
                                    :monday-first="true"
                                    v-model="calendarDay">
                            </date-picker>
                        </div>
                    </div>
                </div>
                <div class="p-3" v-if="timeLogs.length == 0">You haven't worked this day. Slothfully</div>
                <div class="list-group list-group-flush">
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
                            <new-log :projects="projects" :day="currentDay" @logAdded="log => this.timeLogs.push(log)"></new-log>
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
                            <edit-log
                                    v-if="editedTime"
                                    :time="editedTime"
                                    :projects="projects"
                                    :day="currentDay"
                                    @logUpdated="updateLog"
                                    @dateChanged="fetchTimeLogs"
                            ></edit-log>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import TimeLog from '../components/Tracker/TimeLog.vue';
    import NewLog from '../components/Tracker/NewLog.vue';
    import EditLog from '../components/Tracker/EditLog.vue';
    import DatePicker from "vuejs-datepicker";

    export default {
        components: {
            EditLog,
            TimeLog,
            NewLog,
            DatePicker
        },

        data() {
            return {
                currentDay: '',
                calendarDay: null,
                previousDay: '',
                nextDay: '',
                today: '',
                timeLogs: [],
                projects: [],
                editedTime: null
            }
        },

        created() {
            this.today = moment().format('YYYY-MM-DD');
            this.currentDay = moment().format('YYYY-MM-DD');
            this.previousDay = moment(this.currentDay).subtract(1, 'days').format('YYYY-MM-DD');
            this.nextDay = moment(this.currentDay).add(1, 'days').format('YYYY-MM-DD');
            this.fetchData();
        },

        watch: {
            currentDay: function () {
                this.calendarDay = this.currentDay;
            }
        },

        computed: {
            currentDayText: function () {
                return moment(this.currentDay).format('LL');
            }
        },

        methods: {
            /**
             * Reset date filter to todays day
             */
            reset() {
                this.changeDays(this.today);
            },

            /**
             * Change date filter to previous day
             */
            previous() {
                this.changeDays(this.previousDay);
            },

            /**
             * Change date filter to next day
             */
            next() {
                this.changeDays(this.nextDay);
            },

            /**
             * Day change via date picker
             * @param date
             */
            calendarChange(date) {
                let pickedDate = moment(date).format('YYYY-MM-DD');
                this.changeDays(pickedDate);
            },

            /**
             * Change days according to current date filter
             * @param currentDay
             */
            changeDays(currentDay) {
                this.currentDay = currentDay;
                this.previousDay = moment(this.currentDay).subtract(1, 'days').format('YYYY-MM-DD');
                this.nextDay = moment(this.currentDay).add(1, 'days').format('YYYY-MM-DD');
                EventHub.fire('new_current_day', this.currentDay);
                this.fetchTimeLogs();
            },

            /**
             * Delete log by ID
             */
            deleteLog(logId) {
                axios.delete('/api/time/' + logId).then(response => {
                    this.timeLogs = this.timeLogs.filter(item => item.id !== logId);
                    EventHub.fire('log_deleted', logId);
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
                        date: this.currentDay
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
        margin-top: 5% !important;
    }
</style>