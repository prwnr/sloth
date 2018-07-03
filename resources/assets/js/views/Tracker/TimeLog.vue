<template>
    <div class="row form-group log pb-3">
        <div class="col-lg-7">
            <div class="col-lg-12 text-bold p-0">{{ time.project.name }} ({{ time.project.code }})</div>
            <div class="col-lg-12 mt-1 small p-0">
                Description: {{ description }}
            </div>
        </div>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="time" v-model="timeLength">
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
    export default {
        props: ['time'],

        data() {
            return {
                startTime: this.time.start,
                timeLength: null
            }
        },

        created() {
            this.track();
        },

        computed: {
            description: function () {
                return this.time.description ? this.time.description : '(empty)';
            },
        },

        methods: {
            start() {
                this.startTime = moment().utc();
            },

            /**
             * Stops currently running time
             */
            stop() {
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
                    let diff = moment().diff(start);
                    this.timeLength = moment(diff).utc().format('HH:mm:ss');
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