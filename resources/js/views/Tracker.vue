<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Time tracker</h1>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success btn-block"
                                type="button"
                                data-toggle="modal"
                                data-target="#newLog">
                            Add time <i class="fa fa-fw fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="d-inline">Logs from: {{ currentDayText }}</h3>
                    <div class="card-tools">
                        <div class="d-inline ">
                            <div class="btn-group">
                                <button class="btn btn-default"
                                        @click="previous">
                                    <i class="fa fa-angle-left"></i>
                                </button>
                                <button class="btn btn-default"
                                        @click="reset">
                                    today
                                </button>
                                <button class="btn btn-default"
                                        @click="next">
                                    <i class="fa fa-angle-right"></i>
                                </button>
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
                <div>
                    <div class="p-3" v-if="timeLogs.length == 0">You haven't worked this day. Slothfully</div>
                    <div class="list-group list-group-flush">
                        <time-log
                                v-for="time in timeLogs"
                                :key="time.id"
                                :time="time"
                                :projects="projects"
                                @time-edit="handleEditDialog">
                        </time-log>
                    </div>
                    <div class="text-right p-2 d-block"
                         v-if="timeLogs.length != 0">
                        <span class="pr-4">This day you worked for {{ totalTimeWorked }}</span>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="newLog" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content card-primary card-outline">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <new-log
                                    :projects="projects"
                                    :day="currentDay">
                            </new-log>
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
                                    @date-changed="reload">
                            </edit-log>
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
    import Timer from "../utilities/Timer";
    import {mapGetters, mapActions} from 'vuex'

    export default {
        name: 'Tracker',
        components: {
            EditLog,
            TimeLog,
            NewLog,
            DatePicker
        },

        data() {
            return {
                calendarDay: null,
                today: '',
                projects: [],
                editedTime: null,
                timer: new Timer()
            }
        },

        created() {
            this.today = moment().format('YYYY-MM-DD')
            this.current(this.today).catch(error => {
                this.$awn.alert(error.message)
            })
            this.calendarDay = this.currentDay
            this.fetchProjects()
        },

        watch: {
            currentDay: function () {
                this.calendarDay = this.currentDay;
            }
        },

        computed: {
            ...mapGetters({
                authUser: 'authUser',
                totalTime: 'timelogs/totalTime',
                timeLogs: 'timelogs/all',
                currentDay: 'timelogs/currentDay'
            }),
            currentDayText: function () {
                return moment(this.currentDay).format('LL');
            },
            totalTimeWorked: function () {
                return this.timer.format(this.timer.minutesToSeconds(this.totalTime));
            },
        },

        methods: {
            ...mapActions('timelogs', {
                fetch: 'fetch',
                removeLog: 'remove',
                updateLog: 'update',
                next: 'nextDay',
                previous: 'previousDay',
                current: 'currentDay',
            }),

            /**
             * Reset to today
             */
            reset() {
                this.current(this.today)
            },

            /**
             * Reload logs for current day
             */
            reload() {
                this.current(this.currentDay)
            },

            /**
             * Day change via date picker
             * @param date
             */
            calendarChange(date) {
                let pickedDate = moment(date).format('YYYY-MM-DD');
                this.current(pickedDate);
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
            fetchProjects() {
                axios.get('members/' + this.authUser.member.id + '/projects').then(response => {
                    this.projects = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }

    }
</script>

<style scoped>
    .modal-dialog {
        margin-top: 5% !important;
    }
</style>