<template>
    <tr :class="{ deleted: task.is_deleted }">
        <td>
            <span v-if="task.is_deleted">{{ task.name }}</span>
            <input :class="{ 'is-invalid': showError }"
                   class="form-control"
                   type="text"
                   v-if="!task.is_deleted"
                   v-model="task.name">
            <form-error
                    :text="error"
                    :show="showError">
            </form-error>
        </td>
        <td v-if="!task.is_deleted">
            <bootstrap-toggle
                    :options="{
                        on: 'Yes',
                        off: 'No',
                        onstyle: 'success',
                        offstyle: 'danger' }"
                    v-model="task.billable"/>
        </td>
        <td v-if="!task.is_deleted">
            <input :class="{ 'disabled' : !task.billable }"
                   :disabled="!task.billable"
                   class="form-control"
                   min="0"
                   placeholder="Rate"
                   step=".01"
                   type="number"
                   v-model="task.billing_rate"></td>
        <td v-if="!task.is_deleted">
            <select :class="{ 'disabled' : !task.billable }"
                    :disabled="!task.billable"
                    class="form-control"
                    v-model="task.currency">
                <option :value="0" selected disabled>Currency</option>
                <option
                        :key="currency.id"
                        :value="currency.id"
                        v-for="currency in currencies">
                    {{ currency.symbol }} - {{ currency.name }}
                </option>
            </select>
        </td>
        <td class="text-center"
            colspan="3"
            v-if="task.is_deleted">
            task deleted
        </td>
        <td>
            <i @click="destroy"
               class="fa fa-fw fa-trash text-danger"
               title="Delete task"
               v-if="!task.is_deleted">
            </i>
            <i @click="restore"
               class="fa fa-fw fa-undo text-primary"
               title="Restore task"
               v-if="task.is_deleted">
            </i>
        </td>
    </tr>
</template>

<script>
    import String from '../../utilities/String.js';

    export default {
        name: 'TaskRow',
        props: {
            task: {
                type: Object,
                required: true,
            },
            currencies: {
                type: Array,
                required: true,
            },
        },

        data() {
            return {
                error: null
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
                let name = taskName.slugify('_');
                name = name.trim();

                if (!name) {
                    this.error = 'Task name cannot be empty';
                    EventHub.fire('task_error', true);
                    return;
                }

                this.task.type = name;
                if (this.taskExists()) {
                    this.error = 'Task with given name already exists';
                    EventHub.fire('task_error', true);
                    return;
                }

                this.clearError();
            },
        },

        computed: {
            showError() {
                return this.error ? true : false;
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
            },

            /**
             * Clears task duplicated name error and notifys other components about that
             */
            clearError() {
                this.error = null;
                EventHub.fire('task_error', false);
            },

            /**
             * Check if task already exists in parent collection
             * @returns {*}
             */
            taskExists() {
                return this.$parent.$parent.form.tasks.find(item => {
                    return this.task.type == item.type && item.id != this.task.id
                });
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
