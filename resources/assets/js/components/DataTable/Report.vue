<template>
    <div>
        <datatable
                :columns="columns"
                :data="itemsData"
                :total="items.length"
                :query="query"
                :HeaderSettings="headerSettings"
                :Pagination="pagination"
        />

        <div class="col-lg-12 text-right p-3">
            <span class="text-bold">Total hours: </span> {{ totalHours }}
        </div>
    </div>
</template>

<script>
    import StatusBar from './Status';

    export default {
        components: {
            StatusBar
        },

        props: {
            columns: {
                type: Array,
                default: () => [
                    {title: 'Member', field: 'user_name', sortable: true},
                    {title: 'Client', field: 'client', sortable: true},
                    {title: 'Project', field: 'project', sortable: true},
                    {title: 'Task', field: 'task', sortable: true},
                    {title: 'Date', field: 'date', sortable: true},
                    {title: 'Hours', field: 'hours', sortable: true},
                    {title: 'Is billable?', field: 'billable', sortable: true},
                    {title: 'Status', field: 'in_progress', sortable: true, tdComp: StatusBar},
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
                totalHours: this.data.total_hours,
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