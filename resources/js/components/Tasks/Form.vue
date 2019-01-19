<template>
    <div>
        <h5><strong>Project tasks</strong></h5>           
        <table v-if="$parent.form.tasks.length > 0" class="table">
            <thead>
            <tr>
                <td>Name</td>
                <td>Billable</td>
                <td>Billing rate*</td>
                <td>Currency*</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
                <task-row
                        v-for="task in $parent.form.tasks"
                        :key="task.id"
                        :task="task"
                        :currencies="currencies">
                </task-row>
            </tbody>
        </table>
        <p v-else>No tasks assigned to this project</p>    
        <hr class="block mt-5">
        <div class="row">
            <div class="col-4">
                <input :class="{ 'is-invalid': taskError != '' }"
                       @keydown="taskError = ''"
                       class="form-control"
                       id="task_name"
                       name="task_name"
                       placeholder="Name"
                       type="text"
                       v-model="newTask.name">
                    <form-error
                            :text="taskError"
                            :show="showTaskError">
                    </form-error>
            </div>
            <div class="col-2 pl-1">
                <bootstrap-toggle
                        :options="{
                            on: 'Billable',
                            off: 'Non-billable',
                            onstyle: 'success',
                            offstyle: 'danger' }"
                        v-model="newTask.billable"/>
            </div>
            <div class="col-2">
                <input :class="{ 'disabled' : !newTask.billable }"
                       :disabled="!newTask.billable"
                       class="form-control"
                       id="task_billing_rate"
                       name="task_billing_rate"
                       placeholder="Rate"
                       step=".01"
                       type="number"
                       v-model="newTask.billing_rate">
                <form-error
                        :text="$parent.form.errors.get('billing_type')"
                        :show="$parent.form.errors.has('billing_type')">
                </form-error>
            </div>
            <div class="col-3">
                <select :class="{ 'disabled' : !newTask.billable }"
                        :disabled="!newTask.billable"
                        class="form-control"
                        name="task_billing_rate"
                        v-model="newTask.currency">
                    <option :value="0" selected disabled>Currency</option>
                    <option
                            :key="currency.id"
                            :value="currency.id"
                            v-for="currency in currencies">
                        {{ currency.symbol }} {{ currency.name }}
                    </option>
                </select>
            </div>
            <div class="col-1">
                <i @click="addTask"
                   class="fa fa-fw fa-plus-circle text-success"
                   title="Add new task">
                </i>
            </div>
        </div>
        <span class="small">* - optional fields. If task is billable and wont be configured, default project Billing will be used for it</span>
    </div>
</template>

<script>
    import TaskRow from './TaskRow.vue';
    import String from '../../utilities/String.js';

    export default {
        name: 'TasksForm',
        props: {
            tasks: {
                type: Array,
                required: true,
            },
            currencies: {
                type: Array,
                required: true,
            },
            billingTypes: {
                type: Object,
                required: true,
            },
        },
        components: {
            TaskRow
        },
        data() {
            return {
                newTask: {
                    name: '',
                    type: '',
                    billable: true,
                    billing_rate: '',
                    currency: 0,
                    is_deleted: false
                },
                allTasks: [],
                taskError: ''
            }
        },

        watch: {
            'newTask.name': function () {
                let taskName = new String(this.newTask.name);
                this.newTask.type = taskName.slugify('_');
            },
            'newTask.billable': function () {
                if (!this.newTask.billable) {
                    this.newTask.billing_rate = '';
                    this.newTask.currency = 0;
                }
            }
        },

        computed: {
            showTaskError() {
                return this.taskError ? true : false;
            }
        },

        created() {
            EventHub.listen('destroy_task', this.removeTask);
            this.allTasks = this.tasks;
            if (this.allTasks.length == 0) {
                axios.get('projects/task-types').then(response => {
                    this.allTasks = response.data;
                    this.prepareTasksForm();
                }).catch(error => {
                    this.$awn.alert(error.message);
                }); 
            } else {
                this.prepareTasksForm();
            }                  
        },

        destroyed() {
            EventHub.forget('destroy_task');
            this.$parent.form.tasks = [];
        },

        methods: {
            /**
             * Load tasks default data
             */
            prepareTasksForm() {
                this.$parent.form.tasks = this.allTasks.map(item => {
                    let billingRate = '';
                    let currency = 0;
                    if (item.billable) {
                         billingRate = item.billing_rate ? item.billing_rate : this.$parent.form.billing_rate;
                         currency = item.currency_id ? item.currency_id : this.$parent.form.billing_currency;
                    }

                    let task = {
                        type: item.type,
                        name: item.name,
                        billable: !!item.billable,
                        billing_rate: billingRate,
                        currency: currency,
                        is_deleted: typeof item.is_deleted != 'undefined' ? item.is_deleted : false
                    };

                    if (item.id) {
                        task.id = item.id;
                    }

                    return task;
                });
            },

            /**
             * Adds new task to form
             */
            addTask() {
                this.newTask.name = this.newTask.name.trim()
                if (!this.newTask.name) {
                    this.taskError = 'Task name is required';
                    return;
                }

                if (this.$parent.form.tasks.find(item => this.newTask.type == item.type)) {
                    this.taskError = 'Task with given name already exists';
                    return;
                }

                if (this.newTask.billable) {
                    this.newTask.billing_rate = this.newTask.billing_rate ? this.newTask.billing_rate : this.$parent.form.billing_rate;
                    this.newTask.currency = this.newTask.billing_currency ? this.newTask.billing_currency : this.$parent.form.billing_currency;
                }

                this.$parent.form.tasks.push(this.newTask);
                this.newTask = {
                    name: '',
                    type: '',
                    billable: true,
                    billing_rate: '',
                    currency: 0,
                    is_deleted: false
                };
            },

            /**
             * Fallback method listening on destroy_task event
             */
            removeTask(task) {
                let index = this.$parent.form.tasks.indexOf(task);
                this.$parent.form.tasks.splice(index, 1);
            },
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

    i:hover {
        cursor: pointer;
        color: #218838 !important;
    }
</style>