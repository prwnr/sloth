<template>
    <tr :class="{ deleted: task.is_deleted }">
        <td>
            <span v-if="task.is_deleted">{{ task.name }}</span>
            <input v-if="!task.is_deleted" type="text" class="form-control" v-model="task.name" @keydown="clearError" :class="{ 'is-invalid': taskError != '' }">
            <form-error :text="taskError" :show="taskError"></form-error>
        </td>
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
    import String from '../../utilities/String.js';

    export default {
        props: ['task', 'currencies'],

        data() {
            return {
                taskError: ''
            }
        },

        watch: {
            'task.billable': function () {
                if (!this.task.billable) {
                    this.task.billing_rate = '';
                    this.task.currency = 0;
                }
            },
            'task.name': function () {
                let taskName = new String(this.task.name);
                this.task.type = taskName.slugify('_');

                let taskExists = this.$parent.$parent.form.tasks.find(item => {
                    return this.task.type == item.type && item.id != this.task.id
                });
                if (taskExists) {
                    this.taskError = 'Task with given name already exists';
                    EventHub.fire('task_duplicated', true);
                }
            },
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
            },

            /**
             * Clears task duplicated name error and notifys other components about that
             */
            clearError() {
                if (this.taskError) {
                    this.taskError = '';
                    EventHub.fire('task_duplicated', false);
                }
            }
        }
    }
</script>

<style scoped>
    i.fa-trash {
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
