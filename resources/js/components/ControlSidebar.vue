<template>
    <div class="p-3">
        <h5>Your active work logs</h5>
        <hr class="mb-2">
        <span v-if="activeLogs.length == 0">You are too slothful to work on anything right now</span>
        <div :key="log.id"
             class="mb-4"
             v-for="log in activeLogs">
            <h5 class="mb-1">
                {{ log.created_at | formatDateTo('LL') }}
                <button class="btn btn-sm pt-0 pb-0 btn-outline-secondary float-right"
                        @click="stop(log.id)"
                        title="Stop">
                    <i data-v-224c29a5="" class="fa fa-pause"></i>
                </button>
            </h5>
            <div>
                Active since: {{ log.start | formatDateTo('LT') }}
            </div>
            <div>
                Project: {{ log.project.name }}
                <span v-if="log.description"><br>Description: {{ log.description }}</span>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    export default {
        name: 'ControlSidebar',
        data() {
            return {
                logs: []
            }
        },

        created() {
            this.fetchActive().catch(error => {
                this.$awn.alert(error.message)
            });
        },

        filters: {
            formatDateTo: function (date, format) {
                return moment(date).format(format);
            }
        },

        computed: {
            ...mapGetters({
                authUser: 'authUser',
                activeLogs: 'timelogs/active'
            })
        },

        methods: {
            ...mapActions('timelogs', {
                fetchActive: 'fetchActive',
                stopLog: 'stop'
            }),

            stop(id) {
                let timeLog = this.activeLogs.find(item => item.id === id)
                let duration = moment.duration(moment().diff(moment(timeLog.start))).asMinutes()

                this.stopLog({
                    id: id,
                    duration: timeLog.duration + Math.floor(duration)
                }).then(response => {
                    EventHub.fire('timelog_stopped', id)
                }).catch(error => {
                    this.$awn.alert(error.message)
                })
            }
        }
    }
</script>

<style scoped>
    .btn-outline-secondary:hover {
        color: #f8f9fa;
        background-color: inherit;
        border-color: #f8f9fa;
    }
</style>