<template>
    <div class="list-group-item form-group log pb-2" @mouseover="mouseOver = true" @mouseleave="mouseOver = false">
        <div class="row">
            <div class="col-lg-10">
                <div class="col-lg-10 p-0">
                    <strong>{{ time.project.name }} ({{ time.project.code }})</strong>
                    <span v-if="time.task">- {{ time.task.name }} ({{ time.task.billable_text }})</span>
                </div>
                <div class="col-lg-12 mt-1 small p-0">
                    Description: {{ description }}
                </div>
            </div>
            <div class="col-lg-2">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-clock-o" :class="{ 'tick' : startTime }"></i></span>
                    </div>
                    <input :disabled="true"
                           :value="displayTime"
                           class="form-control flat text-right"
                           type="text"
                           v-if="!editing"/>
                    <time-input
                            @keyup.enter.native="update"
                            v-if="editing"
                            v-model="duration">
                    </time-input>
                    <button :disabled="disableStartButton"
                            @click="start"
                            class="btn btn-success btn-flat"
                            title="Start"
                            v-if="!startTime && !editing">
                        <i class="fa fa-play"></i>
                    </button>
                    <button @click="stop"
                            class="btn btn-secondary btn-flat"
                            title="Stop"
                            v-if="startTime">
                        <i class="fa fa-pause"></i>
                    </button>
                    <button
                            @click="update"
                            class="btn btn-primary btn-flat"
                            title="Update"
                            v-if="!startTime && editing">
                        <i class="fa fa-check"></i>
                    </button>
                    <button :disabled="!editing && !authUser.can('edit_time')"
                            :title="editTitle"
                            @click="editing = !editing"
                            class="btn btn-default btn-flat"
                            v-if="!startTime">

                        <i class="fa fa-edit"
                           title="Edit"
                           v-if="!editing">
                        </i>

                        <i class="fa fa-times"
                           title="Cancel"
                           v-if="editing">
                        </i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row pt-1 pb-0">
            <div class="col-lg-12 item-action-buttons">
                <a @click.prevent="editLog"
                   class="small text-primary"
                   data-target="#editRow"
                   data-toggle="modal"
                   href="">
                    <i class="fa fa-edit" title="Edit"></i> edit
                </a>
                <a @click.prevent="deleteLog"
                   class="small text-danger"
                   href="">
                    <i class="fa fa-trash" title="Delete"></i> delete
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";
    import TimeInput from "./TimeInput";
    import {mapGetters, mapActions} from "vuex";

    export default {
        name: 'TimeLog',
        props: {
            time: {
                type: Object,
                required: true
            }
        },
        components: {
            TimeInput
        },

        data() {
            return {
                firstTick: true,
                mouseOver: false,
                editing: false,
                startTime: this.time.start,
                workedTime: null,
                duration: null,
                timer: new Timer(),
            }
        },

        created() {
            this.track();
            if (!this.startTime && this.time.duration > 0) {
                this.workedTime = this.timer.minutesToSeconds(this.time.duration);
                this.duration = this.timer.format(this.workedTime);
            }

            EventHub.listen('timelog_stopped', (id) => {
                if (this.time.id === id) {
                    this.stopCounter()
                }
            })
        },

        destroyed() {
            EventHub.forget('timelog_stopped')
        },

        watch: {
            'time.start': function () {
                this.startTime = this.time.start
            }
        },

        computed: {
            ...mapGetters(['authUser']),
            description: function () {
                return this.time.description ? this.time.description : '(empty)';
            },

            displayTime: function () {
                return this.timer.format(this.workedTime);
            },

            editTitle: function () {
                return this.editing ? 'Cancel' : 'Edit';
            },

            /**
             * Checks if start button should be disabled
             */
            disableStartButton: function () {
                if (this.$parent.currentDay != this.$parent.today) {
                    return true;
                }

                return false;
            },
        },

        methods: {
            ...mapActions('timelogs', {
                updateLog: 'update',
                startLog: 'start',
                stopLog: 'stop',
                updateLogTime: 'updateTime',
                removeLog: 'remove'
            }),

            /**
             * Start stopped time (current time is taken as new start time)
             */
            start() {
                this.startLog({
                    id: this.time.id,
                    duration: this.time.duration
                }).then(response => {
                    this.firstTick = true;
                    this.startTime = response.start.date;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Stops currently running time
             */
            stop() {
                this.stopCounter();
                this.stopLog({
                    id: this.time.id,
                    duration: this.time.duration
                }).catch(error => {
                    this.$awn.alert(error.message)
                })
            },

            stopCounter() {
                let workedSeconds = this.timer.secondsToMinutes(this.workedTime);
                this.time.duration += workedSeconds - this.time.duration;
                this.duration = this.timer.format(this.workedTime);
                this.startTime = null;
            },

            /**
             * Update current log time
             */
            update() {
                let seconds = this.timer.revert(this.duration);
                let newDuration = this.timer.secondsToMinutes(seconds);
                this.updateLogTime({
                    id: this.time.id,
                    duration: newDuration
                }).then(response => {
                    this.editing = false;
                    this.workedTime = seconds;
                    this.time.duration = newDuration;
                    this.duration = this.timer.format(seconds);
                    this.$awn.success('Time log successfully updated');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Sends event with current log data to be processed in popup
             */
            editLog() {
                this.$emit('time-edit', this.time);
            },

            /**
             * Show confirmation popup for log delete and emits events for it
             */
            deleteLog() {
                this.$swal({
                    title: 'Are you sure?',
                    text: 'You won\'t be able to revert this!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#dd4b39',
                    focusCancel: true,
                    reverseButtons: true
                }).then(result => {
                    if (result.value) {
                        if (this.startTime) {
                            this.stopCounter();
                        }

                        axios.delete('time/' + this.time.id).then(response => {
                            this.removeLog(this.time.id)
                            this.$awn.success('Log succesfully deleted.');
                        }).catch(error => {
                            this.$awn.alert(error.message);
                        })
                    }
                })
            },

            /**
             * Start tracking time from current 'startTime' value
             */
            track() {
                this.updateWorkedTime();

                setInterval(() => {
                    if (!this.startTime) {
                        return;
                    }

                    this.updateWorkedTime();
                    this.firstTick = false;
                }, 1000);
            },

            /**
             * Updates worked time with a diff from moment
             */
            updateWorkedTime() {
                let start = moment(this.startTime);
                let workedTime = moment().diff(start, 'seconds') + this.timer.minutesToSeconds(this.time.duration);
                if (!workedTime) {
                    this.workedTime = 0;
                    return;
                }

                this.workedTime = workedTime;
            }
        }
    }
</script>

<style scoped>
    .log-buttons .fa-edit:hover {
        cursor: pointer;
        color: #138496 !important;
    }

    .log-buttons .fa-trash:hover {
        cursor: pointer;
        color: #c82333 !important;
    }
</style>