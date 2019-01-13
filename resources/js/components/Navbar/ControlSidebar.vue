<template>
    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
        <i class="fa fa-th-large"></i>
        <span v-if="activeLogs.length > 0" class="badge badge-success navbar-badge">{{ activeLogs.length }}</span>
    </a>
</template>

<script>
    import {mapGetters} from 'vuex'

    export default {
        computed: {
            ...mapGetters({
                activeLogs: 'timelogs/active'
            })
        }
    }
</script>

<style scoped>

</style>