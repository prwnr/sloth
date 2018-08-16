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
                        <date-range @rangeChange="fetchData"></date-range>
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
    </div>
</template>

<script>
    import StatusBar from '../../components/DataTable/Status';
    import DateRange from '../../components/Report/DateRange';

    export default {
        components: {
            DateRange
        },

        data() {
            return {
                loading: true,
                items: [],
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
            this.fetchData('week');
        },

        computed: {
            itemsData: function () {
                if (this.query.sort) {
                    this.items = _.orderBy(this.items, this.query.sort, this.query.order)
                }

                return this.items.slice(this.query.offset, this.query.offset + this.query.limit)
            }
        },

        methods: {
            /**
             * Get data from API
             * @param range
             */
            fetchData(range) {
                this.loading = true;
                axios.post('/api/reports', {
                    range: range
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