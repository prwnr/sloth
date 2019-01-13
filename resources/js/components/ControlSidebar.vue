<template>
    <div class="p-3">
        <h5>Your active work logs</h5>
        <hr class="mb-2">
        <span v-if="activeLogs.length == 0">You are too slothful to work on anything right now</span>
        <div class="mb-4" v-for="log in activeLogs" :key="log.id">
            <h5 class="mb-1">{{ log.created_at | formatDateTo('LL') }}</h5>
            <div>Active since: {{ log.start | formatDateTo('LT') }}</div>
            {{ log.project.name }} <span v-if="log.description">- {{ log.description }}</span>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions} from 'vuex';

    export default {
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
            ...mapActions('timelogs', [
                'fetchActive'
            ]),
        }
    }
</script>

<style scoped>

</style>