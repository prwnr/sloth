<template>
    <section class="content">
        <div class="card card-table">
            <div class="card-header">
                <h3 class="d-inline">My report</h3>
                <div class="card-tools">
                    <filters class="mr-2" :disable-filters="['members']" @applied="applyFilters"></filters>
                    <date-range @change="applyRangeFilter"></date-range>
                </div>
            </div>
            <div class="card-body p-0">
                <loading v-if="loading"></loading>

                <loading v-if="loading"></loading>
                <report v-if="!loading" :show-salary="false" :data="reportData" :columns="columns"></report>
            </div>
        </div>
    </section>
</template>

<script>
    import Report from '../../components/DataTable/Report';
    import Filters from '../../components/Report/Filters';
    import DateRange from '../../components/Report/DateRange';
    import {mapGetters} from 'vuex';

    export default {
        components: {
            DateRange, Filters, Report
        },

        data() {
            return {
                loading: true,
                reportData: [],
                filters: {
                    range: 'week',
                    members: [],
                    clients: [],
                    projects: [],
                    billable: [],
                    status: 3,
                },
                columns: [
                    {title: 'Member', field: 'user_name', sortable: true},
                    {title: 'Client', field: 'client', sortable: true},
                    {title: 'Project', field: 'project', sortable: true},
                    {title: 'Task', field: 'task', sortable: true},
                    {title: 'Date', field: 'date', sortable: true},
                    {title: 'Hours', field: 'duration', sortable: true},
                    {title: 'Status', field: 'in_progress', sortable: true, tdComp: 'StatusBar'},
                ]
            }
        },

        created() {
            this.filters.members.push(this.authUser.member.id);
            this.fetchData();
        },

        computed: {
            ...mapGetters(['authUser'])
        },

        methods: {
            /**
             * @param filters
             */
            applyFilters(filters) {
                this.filters.members = filters.members;
                this.filters.projects = filters.projects;
                this.filters.clients = filters.clients;
                this.filters.billable = filters.billable;
                this.filters.status = filters.status;
                this.fetchData();
            },

            /**
             * @param range
             */
            applyRangeFilter(range) {
                this.filters.range = range;
                this.fetchData();
            },

            /**
             * Get data from API
             */
            fetchData() {
                this.loading = true;
                axios.post('/api/reports/' + this.authUser.member.id, {
                    filters: this.filters
                }).then(response => {
                    this.reportData = response.data;
                    this.loading = false;
                }).catch(error => {
                    this.$awn.alert(error.message);
                }).finally(() => {
                    this.loading = false;
                });
            }
        }
    }
</script>