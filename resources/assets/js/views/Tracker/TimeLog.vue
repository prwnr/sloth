<template>
    <div class="list-group-item form-group log pb-4" @mouseover="mouseOver = true" @mouseleave="mouseOver = false">
        <div class="row">
            <div class="col-lg-10">
                <div class="col-lg-10 p-0">
                    <strong>{{ time.project.name }} ({{ time.project.code }})</strong>
                    <span v-if="time.task">- {{ time.task.name }} ({{ time.task.billable_text }})</span>
                    <span class="log-buttons pl-3" v-if="mouseOver">
                    <span><i class="text-info fa fa-edit p-1" @click="editLog" data-toggle="modal" data-target="#editRow"></i></span>
                    <span><i class="text-danger fa fa-trash p-1"  @click="deleteLog"></i></span>
                </span>
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
                    <input v-if="!editing" type="text" class="form-control flat text-right" :disabled="true" :value="displayTime"/>
                    <input v-if="editing" type="text" class="form-control flat text-right" name="time"
                           v-model="duration"
                           v-mask="'##:##'"
                           placeholder="00:00"
                           @keyup="correctTime"/>
                    <button v-if="!startTime && !editing" :disabled="disableStartButton" class="btn btn-success btn-flat" @click="start" title="Start">
                        <i class="fa fa-play"></i>
                    </button>
                    <button v-if="startTime" class="btn btn-secondary btn-flat" @click="stop" title="Stop">
                        <i class="fa fa-pause"></i>
                    </button>
                    <button v-if="!startTime && editing" class="btn btn-primary btn-flat" @click="update" title="Update">
                        <i class="fa fa-check"></i>
                    </button>
                    <button v-if="!startTime" class="btn btn-default btn-flat"
                            :title="editTitle"
                            @click="editing = !editing">
                        <i v-if="!editing" class="fa fa-edit" title="Edit"></i>
                        <i v-if="editing" class="fa fa-times" title="Cancel"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";

    export default {
        props: ['time'],

        data() {
            return {
                mouseOver: false,
                editing: false,
                startTime: this.time.start,
                workedTime: null,
                duration: null,
                timer: new Timer()
            }
        },

        created() {
            this.track();
            if (!this.startTime && this.time.duration > 0) {
                this.workedTime = this.timer.minutesToSeconds(this.time.duration);
                this.duration = this.timer.format(this.workedTime);
            }
        },

        computed: {
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
            /**
             * Start stopped time (current time is taken as new start time)
             */
            start() {
                axios.put('/api/time/' + this.time.id + '/duration', {
                    duration: this.time.duration,
                    time: 'start'
                }).then(response => {
                    this.startTime = response.data.data.start.date;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Stops currently running time
             */
            stop() {
                let workedSeconds = this.timer.secondsToMinutes(this.workedTime);
                this.time.duration += workedSeconds - this.time.duration;
                this.duration = this.timer.format(this.workedTime);
                this.startTime = null;
                axios.put('/api/time/' + this.time.id + '/duration', {
                    duration: this.time.duration,
                    time: 'stop'
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Update current log time
             */
            update() {
                let seconds = this.timer.revert(this.duration);
                let newDuration = this.timer.secondsToMinutes(seconds);
                axios.put('/api/time/' + this.time.id + '/duration', {
                    duration: newDuration,
                }).then(response => {
                    this.editing = false;
                    this.workedTime = seconds;
                    this.time.duration = newDuration;
                    this.$awn.success('Time log successfully updated');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Sends event with current log data to be processed in popup
             */
            editLog() {
                this.$emit('editTime', this.time);
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
                        this.$emit('logDeleted', this.time.id);
                    }
                })
            },

            /**
             * Makes sure that MM in HH:MM won't go over 60 minutes
             */
            correctTime() {
                let time = this.duration.split(':');
                if (time.length != 2) {
                    return;
                }

                if (time[1] > 60) {
                    this.duration = time[0] + ':' + 60;
                    return;
                }
            },

            /**
             * Start tracking time from current 'startTime' value
             */
            track() {
                setInterval(() => {
                    if (!this.startTime) {
                        return;
                    }

                    let start = moment(this.startTime);
                    this.workedTime = moment().diff(start, 'seconds') + this.timer.minutesToSeconds(this.time.duration);
                }, 1000);
            },
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

    .tick {
        -webkit-animation: tick 1s;
        -moz-animation: tick 1s;
        animation: tick 1s;
        -webkit-animation-iteration-count: infinite;
        -moz-animation-iteration-count: infinite;
        animation-iteration-count: infinite;
    }

    @keyframes tick {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
</style>