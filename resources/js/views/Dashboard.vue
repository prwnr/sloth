<template>
    <div class="content">
        <section class="content-header"></section>
        <section class="content">
            <div class="col-lg-12">
                <div v-if="authUser.can('view_reports')" class="row">
                    <div class="col-12">
                        <h2>Reports charts</h2>
                    </div>
                    <div class="col-12">
                        <sales :key="authUser.member.id"></sales>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h2>Personal charts</h2>
                    </div>
                    <div class="col-6">
                        <total-hours :key="authUser.member.id"></total-hours>
                    </div>
                    <div class="col-6 ">
                        <projects-hours :key="authUser.member.id"></projects-hours>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import TotalHours from '../components/Dashboard/TotalHours';
    import ProjectsHours from '../components/Dashboard/ProjectsHours';
    import Sales from '../components/Dashboard/Sales';
    import {mapGetters} from 'vuex'

    export default {
        name: 'Dashboard',
        components: {
            TotalHours, ProjectsHours, Sales
        },

        data() {
            return {
                user: {},
                key: 0
            }
        },

        created() {
            this.user = this.authUser
            this.key = this.authUser.member.id
            EventHub.listen('dashboard_change', () => {
                this.user = this.authUser;
            })
        },

        computed: {
            ...mapGetters(['authUser'])
        },

        destroyed() {
            EventHub.forget('dashboard_change');
        },
    }
</script>
