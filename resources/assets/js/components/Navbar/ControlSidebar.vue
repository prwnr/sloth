<template>
    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
        <i class="fa fa-th-large"></i>
        <span v-if="logs" class="badge badge-success navbar-badge">{{ logs }}</span>
    </a>
</template>

<script>
    export default {
        data() {
            return {
                logs: 0
            }
        },

        created() {
            EventHub.listen('sidebar_logs_loaded', this.initialCount);
            EventHub.listen('log_created', this.increment);
            EventHub.listen('log_deleted', this.decrement);
        },

        methods: {
            initialCount(logs) {
                this.logs = logs.length;
            },
            increment(log) {
                this.logs++;
            },
            decrement(log) {
                this.logs--;
            }
        }
    }
</script>

<style scoped>

</style>