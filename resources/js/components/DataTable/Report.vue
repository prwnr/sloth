<template>
    <div class="report">
        <datatable
                :columns="columns"
                :data="itemsData"
                :total="items.length"
                :query="query"
                :HeaderSettings="headerSettings"
                :Pagination="pagination"
                :support-nested="true"
        />

        <div class="col-lg-12 text-left p-3 border-top">
            <span class="pr-2">
                <span class="text-bold">Total hours: </span> {{ totals.hours }}
            </span>
            <span class="pr-2">
                <span class="text-bold">Total billable hours: </span> {{ totals.billable_hours }}
            </span>
            <div v-if="showSalary">
                <div class="text-bold">Total salary: <span v-if="totals.salary.length == 0">none</span></div>
                <span v-if="totals.salary" v-for="(total, currency) in totals.salary" :key="currency">
                    <strong class="pl-1">{{ currency }}</strong>: {{ total }};
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import StatusBar from './Status';
    import Operations from './Operations';
    import NestedRow from './NestedRow';

    export default {
        name: 'Report',
        components: {
            StatusBar, Operations, NestedRow
        },

        props: {
            showSalary: {
                type: Boolean,
                default: true
            },
            columns: {
                type: Array,
                default: () => [
                    {title: 'Member', field: 'user_name', sortable: true},
                    {title: 'Client', field: 'client', sortable: true},
                    {title: 'Project', field: 'project', sortable: true},
                    {title: 'Task', field: 'task', sortable: true},
                    {title: 'Date', field: 'date', sortable: true},
                    {title: 'Hours', field: 'duration', sortable: true},
                    {title: 'Is billable?', field: 'billable', sortable: true},
                    {title: 'Status', field: 'in_progress', sortable: true, tdComp: StatusBar},
                    {title: 'Operation', tdComp: Operations}
                ],
            },
            data: {
                type: Object,
                default: () => {
                    return {
                        items: [], totalHours: 0
                    }
                }
            },
            query: {
                type: Object,
                default: () => {
                    return {
                        sort: 'date', order: 'desc'
                    }
                }
            },
            headerSettings: {
                type: Boolean,
                default: false
            },
            pagination: {
                type: Boolean,
                default: false
            }
        },

        data() {
            return {
                items: this.data.items,
                totals: this.data.totals,
            }
        },

        computed: {
            itemsData: function () {
                if (this.query.sort) {
                    this.items = _.orderBy(this.items, this.query.sort, this.query.order)
                }

                return this.items;
            }
        },
    }
</script>

<style scoped>

</style>