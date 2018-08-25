<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Reports</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="card card-table">
                <div class="card-header">
                    <h3 class="d-inline">Detailed reports</h3>
                    <div class="card-tools">
                        <filters class="mr-2" @applied="applyFilters"></filters>
                        <date-range @rangeChange="applyRangeFilter"></date-range>
                    </div>
                </div>
                <div class="card-body p-0">
                    <loading v-if="loading"></loading>
                    <report v-if="!loading" :data="reportData"></report>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import Report from '../../components/DataTable/Report';
    import Filters from '../../components/Report/Filters';
    import DateRange from '../../components/Report/DateRange';

    export default {
        components: {
            DateRange, Filters, Report
        },

        data() {
            return {
                loading: true,
                reportData: null,
                filters: {
                    range: 'week',
                    members: [],
                    clients: [],
                    projects: [],
                    billable: []
                }
            }
        },

        created() {
            this.fetchData();
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
                axios.post('/api/reports', {
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