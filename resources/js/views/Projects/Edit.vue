<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Project: {{ form.name }}</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                exact
                                v-if="project.id"
                                :to="{ name: 'projects.show', params: { id: project.id } }"
                                class="btn btn-info btn-block ">View
                        </router-link>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <form @submit.prevent="submitForm" @keydown="form.errors.clear($event.target.name)">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Details</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input id="name"
                                           type="text"
                                           class="form-control"
                                           :class="{ 'is-invalid': form.errors.has('name')}"
                                           name="name"
                                           placeholder="Name"
                                           required
                                           v-model="form.name">
                                    <form-error
                                            :text="form.errors.get('name')"
                                            :show="form.errors.has('name')">

                                    </form-error>
                                </div>

                                <div class="form-group">
                                    <label for="code">Code</label>
                                    <div class="input-group">
                                        <input id="code"
                                               type="text"
                                               class="form-control"
                                               :class="{ 'is-invalid': form.errors.has('code')}"
                                               name="code"
                                               placeholder="Code"
                                               required
                                               v-model="form.code"
                                               :maxlength="maxCodeLenght">
                                        <div class="input-group-append">
                                            <span class="input-group-text"
                                                  v-text="(maxCodeLenght - form.code.length)">
                                            </span>
                                        </div>
                                    </div>
                                    <form-error
                                            :text="form.errors.get('code')"
                                            :show="form.errors.has('code')">
                                    </form-error>
                                </div>

                                <label for="budget">Budget</label>
                                <div class="form-group row">
                                    <div class="col-4">
                                        <input id="budget"
                                               type="number"
                                               step=".01"
                                               class="form-control"
                                               :class="{ 'is-invalid': form.errors.has('budget')}"
                                               name="budget"
                                               placeholder="Budget"
                                               required
                                               v-model="form.budget">
                                        <form-error
                                                :text="form.errors.get('budget')"
                                                :show="form.errors.has('budget')">
                                        </form-error>
                                    </div>

                                    <div class="col-4 ">
                                        <select class="form-control"
                                                :class="{ 'is-invalid': form.errors.has('budget_currency')}"
                                                name="budget_currency"
                                                id="budget_currency"
                                                required
                                                v-model="form.budget_currency">
                                            <option value="0" selected disabled>Currency</option>
                                            <option
                                                    v-for="currency in currencies"
                                                    :key="currency.id"
                                                    :value="currency.id">
                                                {{ currency.symbol }} {{ currency.name }}
                                            </option>
                                        </select>
                                        <form-error
                                                :text="form.errors.get('budget_currency')"
                                                :show="form.errors.has('budget_currency')">
                                        </form-error>
                                    </div>

                                    <div class="col-4">
                                        <select class="form-control"
                                                :class="{ 'is-invalid': form.errors.has('budget_period')}"
                                                name="budget_period"
                                                id="budget_period"
                                                required
                                                v-model="form.budget_period">
                                            <option value="" selected disabled>Budget period</option>
                                            <option
                                                    v-for="(name, index) in budgetPeriods"
                                                    :key="index"
                                                    :value="index">
                                                {{ name }}
                                            </option>
                                        </select>
                                        <form-error
                                                :text="form.errors.get('budget_period')"
                                                :show="form.errors.has('budget_period')">
                                        </form-error>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <card-header>Client</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2
                                            v-model="form.client"
                                            :options="clientsSelectOptions"
                                            :class="{ 'is-invalid': form.errors.has('client')}"
                                            @change="form.errors.clear('client')"></Select2>
                                </div>
                                <form-error
                                        :text="form.errors.get('client')"
                                        :show="form.errors.has('client')">
                                </form-error>
                            </div>
                        </div>
                        <button class="mt-3 btn btn-success w-25"
                                :disabled="taskHasError">
                            Save
                        </button>
                    </div>

                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Assigned members</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2
                                            v-model="form.members"
                                            :options="membersSelectOptions"
                                            :settings="{ multiple: true }"
                                            :class="{ 'is-invalid': form.errors.has('members')}"
                                            @change="form.errors.clear('members')">
                                    </Select2>
                                </div>
                                <form-error
                                        :text="form.errors.get('members')"
                                        :show="form.errors.has('members')">

                                </form-error>
                            </div>
                        </div>

                        <div class="card">
                            <card-header>Billings</card-header>
                            <div class="card-body">
                                <billings-form
                                        v-if="currencies.length > 0 && billingTypes"
                                        :form="form"
                                        :currencies="currencies"
                                        :billingTypes="billingTypes">
                                </billings-form>
                                <hr>
                                <button class="btn btn-info mb-3"
                                        type="button"
                                        v-if="canAddTasks"
                                        @click="addTasks">
                                    Add tasks
                                </button>
                                <button class="btn btn-info mb-3"
                                        type="button" v-else-if="!allTasksDeleted"
                                        @click="deleteTasks" >
                                    Delete tasks
                                </button>
                                <button class="btn btn-info mb-3"
                                        type="button"
                                        v-else-if="allTasksDeleted"
                                        @click="restoreTasks">
                                    Restore tasks
                                </button>
                                <tasks-form
                                        v-if="(currencies.length > 0 && billingTypes && tasks) && showTasks"
                                        :tasks="tasks"
                                        :currencies="currencies"
                                        :billingTypes="billingTypes">
                                </tasks-form>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</template>

<script>
    import TasksForm from '../../components/Tasks/Form.vue';
    import BillingsForm from '../../components/Billings/Form.vue';

    export default {
        name: 'ProjectsEdit',
        components: {
            BillingsForm,
            TasksForm
        },
        data() {
            return {
                showTasks: true,
                project: {},
                maxCodeLenght: 4,
                clients: [],
                members: [],
                currencies: [],
                billingTypes: null,
                budgetPeriods: [],
                taskHasError: false,
                tasks: null,
                form: new Form({
                    name: '',
                    code: '',
                    budget: '',
                    budget_currency: 0,
                    budget_period: '',
                    client: null,
                    members: [],
                    billing_rate: '',
                    billing_type: '',
                    billing_currency: 0,
                    tasks: []
                })
            }
        },

        created() {
            this.fetchData();
            EventHub.listen('task_error', this.taskErrorHandle);
        },

        watch: {
            'form.code': function () {
                let code = this.form.code.toString().toUpperCase();
                this.form.code = code;
            },
            'form.tasks': function () {
                if (this.form.tasks.length == 0) {
                    this.showTasks = false;
                }
            }
        },

        computed: {
            membersSelectOptions: function () {
                return this.members.map(function (item) {
                    return {
                        id: item.id,
                        text: item.user.fullname
                    }
                });
            },
            clientsSelectOptions: function () {
                return this.clients.map(function (item) {
                    return {
                        id: item.id,
                        text: item.company_name
                    }
                });
            },
            allTasksDeleted: function () {
                if (this.form.tasks.every(item => {
                    return item.is_deleted;
                })) {
                    return true;
                }

                return false;
            },
            canAddTasks: function () {
                if (this.form.tasks === null || this.form.tasks.length == 0) {
                    return true;
                }

                return false;
            },
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.put('projects/' + this.$route.params.id).then(response => {
                        this.$awn.success('Project updated successfully.');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })
                );
            },

            /**
             * Dispatch event that all tasks should be marked as deleted
             */
            deleteTasks() {
                this.form.tasks = this.form.tasks.filter(item => {
                    return typeof item.id != 'undefined';
                });

                this.form.tasks.map(item => {
                    return item.is_deleted = true;
                });
            },

            /**
             * Dispatch event that all tasks should be retored
             */
            restoreTasks() {
                this.form.tasks.map((item) => {
                    return item.is_deleted = false;
                });
            },

            /**
             *  Add tasks form to project
             */
            addTasks() {
                this.$swal({
                    title: 'Are you sure?',
                    text: 'Once this project will be saved with tasks added to it, you won\'t be able to fully delete those tasks!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    confirmButtonColor: '#28a745',
                    focusCancel: true,
                    reverseButtons: true
                }).then(result => {
                    if (result.value) {
                        axios.get('projects/task-types').then(response => {
                            this.tasks = response.data;
                            this.showTasks = true;
                        }).catch(error => {
                            this.$awn.alert(error.message);
                        });
                    }
                });
            },

            /**
             * Handles task_duplication event
             */
            taskErrorHandle(isDuplicated) {
                this.taskHasError = isDuplicated;
            },

            /**
             * Load all required data
             */
            fetchData() {
                this.fetchProject();
                this.fetchClients();
                this.fetchMembers();
                this.fetchBudgetData();
            },
            /**
             * Load current project data
             */
            fetchProject() {
                axios.get('projects/' + this.$route.params.id).then(response => {
                    this.project = response.data.data;
                    this.form.name = this.project.name;
                    this.form.code = this.project.code;
                    this.form.budget = this.project.budget;
                    this.form.budget_currency = this.project.budget_currency.id;
                    this.form.budget_period = this.project.budget_period;
                    this.form.client = this.project.client.id;
                    this.form.members = this.project.members.map(item => item.id);
                    this.form.billing_rate = this.project.billing.rate;
                    this.form.billing_type = this.project.billing.type;
                    this.form.billing_currency = this.project.billing.currency.id;
                    this.tasks = this.project.tasks;
                    if (this.tasks.length == 0) {
                        this.showTasks = false;
                    }
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load clients 
             */
            fetchClients() {
                axios.get('clients').then(response => {
                    this.clients = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load members 
             */
            fetchMembers() {
                axios.get('members').then(response => {
                    this.members = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load currencies and budget periods 
             */
            fetchBudgetData() {
                axios.get('billings/data').then(response => {
                    this.currencies = response.data.currencies;
                    this.budgetPeriods = response.data.budget_periods;
                    this.billingTypes = response.data.billing_types;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }
    }
</script>

<style scoped>

</style>