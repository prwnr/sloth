<template>
    <div class="row form-group log pb-3">
        <div class="col-lg-7">
            <div class="col-lg-12 text-bold p-0">{{ time.project.name }} ({{ time.project.code }})</div>
            <div class="col-lg-12 mt-1 small p-0">
                Description: {{ description }}
            </div>
        </div>
        <div class="col-lg-2">
            <input v-if="startTime" :disabled="true" type="text" class="form-control" v-model="displayTime">
            <input v-if="!startTime" type="text" class="form-control" name="time" v-model="length">
        </div>
        <div class="col-lg-3">
            <button v-if="!startTime" class="btn btn-success" @click="start">Start</button>
            <button v-if="startTime" class="btn btn-warning" @click="stop">Stop</button>
            <button class="btn btn-default">Edit</button>
            <button class="btn btn-danger">Delete</button>
        </div>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";

    export default {
        props: ['time'],

        data() {
            return {
                startTime: this.time.start,
                workedTime: null,
                length: null,
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
            }
        },

        methods: {
            start() {
                this.startTime = moment().utc();
            },

            /**
             * Stops currently running time
             */
            stop() {
                let workedSeconds = this.timer.secondsToMinutes(this.workedTime);
                this.time.length += workedSeconds - this.time.length;
                this.length = this.timer.format(this.workedTime);
                this.startTime = null;
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
                    this.workedTime = moment().diff(start, 'seconds') + this.timer.minutesToSeconds(this.time.length);
                }, 1000);
            },
        }
    }
</script>

<style scoped>
    .log {
        border-bottom: 1px solid #ccc;
    }
</style>