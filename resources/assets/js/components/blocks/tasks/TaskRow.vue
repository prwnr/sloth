<template>
    <tr :class="{ deleted: task.is_deleted }">
        <td>{{ name }}</td>
        <td v-if="!task.is_deleted">
            <bootstrap-toggle v-model="task.billable" :options="{
                    on: 'Yes',
                    off: 'No',
                    onstyle: 'success',
                    offstyle: 'danger' }"/>
        </td>
        <td v-if="!task.is_deleted">
            <input :disabled="!task.billable"
                :class="{ 'disabled' : !task.billable }"
                class="form-control" 
                type="number" 
                step=".01" 
                min="0" 
                placeholder="Rate" 
                v-model="task.billing_rate"></td>
        <td v-if="!task.is_deleted">
            <select :disabled="!task.billable" :class="{ 'disabled' : !task.billable }" class="form-control" v-model="task.currency">
                <option :value="0" selected disabled>Currency</option>
                <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                    {{ currency.symbol }} - {{ currency.name }}
                </option>
            </select>
        </td>
        <td v-if="task.is_deleted" colspan="3" class="text-center">
            task deleted
        </td>
        <td>
            <i v-if="!task.is_deleted" class="fa fa-fw fa-trash text-danger" @click="destroy" title="Delete task"></i>
            <i v-if="task.is_deleted" class="fa fa-fw fa-undo text-primary" @click="restore" title="Restore task"></i>
        </td>
    </tr>
</template>

<script>
    export default {
        props: ['name', 'task', 'currencies'],

        watch: {
            'task.billable': function () {
                if (!this.task.billable) {
                    this.task.billing_rate = '';
                    this.task.currency = 0;
                }
            }
        },

        methods: {
            /**
             * Marks task as deleted or destroy it if it is a new one
             */
            destroy() {
                if (!this.task.id || typeof this.task.id == 'undefined') {
                    EventHub.fire('destroy_task', this.task);
                    return;
                }

                this.task.is_deleted = true;
            },

            /**
             * Restore deleted task
             */
            restore() {
                this.task.is_deleted = false;
            }
        }
    }
</script>

<style scoped>
    i {
        -webkit-transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, -webkit-box-shadow 0.15s ease-in-out;
        font-size: 25px;
        line-height: 35px;
    }

    i.text-danger:hover {
        cursor: pointer;
        color: #c82333 !important;
    }

    i.text-primary:hover {
        cursor: pointer;
        color: #0069d9 !important;
    }

    .deleted {
        background-color: rgba(0, 0, 0, 0.075);
    }
</style>
