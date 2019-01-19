<template>
    <div class="mt-4">
        <h5><strong>Tasks</strong></h5>             
        <table class="table table mt-2">
            <thead>
            <tr>
                <th>Name</th>
                <th>Billable</th>
                <th>Billing rate</th>
                <th>Currency</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                <tr :class="{ deleted: task.is_deleted }"
                    :key="task.id"
                    v-for="task in tasks">

                    <td>
                        {{ task.name }}
                    </td>

                    <td v-if="!task.is_deleted">
                        {{ task.billable_text }}
                    </td>

                    <td v-if="!task.is_deleted">
                        <span v-if="task.billable">
                            {{ task.billing_rate }}
                        </span>
                        <span v-if="!task.billable">
                            none
                        </span>
                    </td>

                    <td v-if="!task.is_deleted">
                        <span v-if="task.billable">
                            {{ task.currency.name }} ({{ task.currency.symbol }})
                        </span>
                        <span v-if="!task.billable">
                            none
                        </span>
                    </td>

                    <td class="text-center"
                        colspan="4"
                        v-if="task.is_deleted">
                        task deleted
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    export default {
        name: 'TasksShow',
        props: {
            tasks: {
                type: Object|Array,
                required: true
            }
        }
    }
</script>

<style scoped>
    .deleted {
        background-color: rgba(0, 0, 0, 0.075);
    }
</style>