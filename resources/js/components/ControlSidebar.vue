<template>
    <div class="p-3">
        <h5>Your active work logs</h5>
        <hr class="mb-2">
        <span v-if="logs.length == 0">You are too slothful to work on anything right now</span>
        <div class="mb-4" v-for="log in logs" :key="log.id">
            <h5 class="mb-1">{{ log.created_at | formatDateTo('LL') }}</h5>
            <div>Active since: {{ log.start | formatDateTo('LT') }}</div>
            {{ log.project.name }} <span v-if="log.description">- {{ log.description }}</span>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                logs: []
            }
        },

        created() {
            EventHub.listen('log_updated', this.updateLogs);
            EventHub.listen('log_deleted', this.removeLog);
            EventHub.listen('log_created', this.addLog);
            this.fetchTimeLogs();
        },

        filters: {
            formatDateTo: function (date, format) {
                return moment(date).format(format);
            }
        },

        methods: {
            /**
             * Update logs
             * @param log
             */
            updateLogs(log) {
                this.fetchTimeLogs();
            },

            /**
             * Add log to list
             * @param log
             */
            addLog(log) {
                if (!log.start) {
                    return;
                }

                this.logs.push(log);
            },

            /**
             * Remove log from list
             * @param id
             */
            removeLog(id) {
                this.logs = this.logs.filter(item => item.id !== id);
            },

            /**
             * Fetch logs
             */
            fetchTimeLogs() {
                axios.get('/api/users/' + this.$user.data.id + '/logs', {
                    params: {
                        active: true
                    }
                }).then(response => {
                    this.logs = response.data.data;
                    EventHub.fire('sidebar_logs_loaded', this.logs);
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>