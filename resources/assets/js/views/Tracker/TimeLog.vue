<template>
    <div class="row form-group log pb-3">
        <div class="col-lg-9">
            <div class="col-lg-12 p-0"><strong>{{ time.project.name }} ({{ time.project.code }})</strong> - {{ time.task.name }} ({{ time.task.billable_text }})</div>
            <div class="col-lg-12 mt-1 small p-0">
                Description: {{ description }}
            </div>
        </div>
        <div class="col-lg-2">
            <div class="input-group">
                <input v-if="!editing" type="text" class="form-control flat text-right" :disabled="true" :value="displayTime"/>
                <input v-if="editing" type="text" class="form-control flat text-right" name="time"
                       v-model="duration"
                       v-mask="'##:##'"
                       @keyup="correctTime"/>
                <button v-if="!startTime && !editing" class="btn btn-success btn-flat" @click="start">Start</button>
                <button v-if="startTime" class="btn btn-warning btn-flat" @click="stop">Stop</button>
                <button v-if="!startTime && editing" class="btn btn-success btn-flat" @click="update">Update</button>
                <button v-if="!startTime" class="btn btn-default btn-flat" @click="editing = !editing">{{ editButtonText }}</button>
            </div>
        </div>
        <div class="col-lg-1 text-right">
            <i class="btn fa fa-trash text-danger" @click="deleteLog"></i>
        </div>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";

    export default {
        props: ['time'],

        data() {
            return {
                editing: false,
                startTime: this.time.start,
                workedTime: null,
                duration: null,
                timer: new Timer()
            }
        },

        created() {
            this.track();
        },

        computed: {
            description: function () {
                return this.time.description ? this.time.description : '(empty)';
            },

            displayTime: function () {
                return this.timer.format(this.workedTime);
            },

            editButtonText: function () {
                return this.editing ? 'Cancel' : 'Edit';
            }
        },

        methods: {
            start() {
                this.editing = false;
                this.startTime = moment().utc();
            },

            /**
             * Stops currently running time
             */
            stop() {
                let workedSeconds = this.timer.secondsToMinutes(this.workedTime);
                this.time.duration += workedSeconds - this.time.duration;
                this.duration = this.timer.format(this.workedTime);
                this.startTime = null;
            },

            /**
             *
             */
            update() {

            },

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
                        this.$emit('logDeleted', this.time);
                    }
                })
            },

            /**
             *
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
    .log {
        border-bottom: 1px solid #ccc;
    }

    .fa-trash {
        font-size: 28px;
        padding: 0 !important;
    }

    .fa-trash:hover {
        cursor: pointer;
        color: #c82333 !important;
    }
</style>