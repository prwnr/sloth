<template>
    <section class="content">
        <div class="card card-table">
            <div class="card-header">
                <h3 class="d-inline">My report</h3>
                <div class="card-tools">
                    <filters class="mr-2" :disable-filters="['members']" @applied="applyFilters"></filters>
                    <date-range @rangeChange="applyRangeFilter"></date-range>
                </div>
            </div>
            <div class="card-body p-0">
                <loading v-if="loading"></loading>

                <datatable
                        v-if="!loading"
                        :columns="columns"
                        :data="itemsData"
                        :total="items.length"
                        :query="query"
                        :HeaderSettings="false"
                        :Pagination="false"
                />

                <div class="col-lg-12 text-right p-3">
                    <span class="text-bold">Total hours: </span> {{ totalHours }}
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    import StatusBar from '../../components/DataTable/Status';
    import Filters from '../../components/Report/Filters';
    import DateRange from '../../components/Report/DateRange';

    export default {
        components: {
            DateRange, Filters
        },

        data() {
            return {
                loading: true,
                items: [],
                filters: {
                    range: 'week',
                    members: [this.$user.get('id')],
                    clients: [],
                    projects: [],
                    billable: []
                },
                totalHours: 0,
                columns: [
                    {title: 'Member', field: 'user_name', sortable: true},
                    {title: 'Client', field: 'client', sortable: true},
                    {title: 'Project', field: 'project', sortable: true},
                    {title: 'Task', field: 'task', sortable: true},
                    {title: 'Date', field: 'date', sortable: true},
                    {title: 'Hours', field: 'hours', sortable: true},
                    {title: 'Is billable?', field: 'billable', sortable: true},
                    {title: 'Status', field: 'in_progress', sortable: true, tdComp: StatusBar},
                ],
                query: {sort: 'date', order: 'desc'},
            }
        },

        created() {
            this.fetchData();
        },

        computed: {
            itemsData: function () {
                if (this.query.sort) {
                    this.items = _.orderBy(this.items, this.query.sort, this.query.order);
                }

                return this.items;
            }
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
                axios.post('/api/reports/' + this.$user.get('id'), {
                    filters: this.filters
                }).then(response => {
                    this.items = response.data.items;
                    this.totalHours = response.data.total_hours;
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