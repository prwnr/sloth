<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Create new project</h1>
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
                                    <input id="name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.has('name')}"
                                           name="name" value="" placeholder="Name" required v-model="form.name">
                                    <form-error :text="form.errors.get('name')" :show="form.errors.has('name')"></form-error>
                                </div>

                                <div class="form-group">
                                    <label for="code">Code</label>
                                    <div class="input-group">
                                        <input id="code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.has('code')}"
                                               name="code" value="" placeholder="Code" required v-model="form.code" :maxlength="maxCodeLenght">
                                        <div class="input-group-append">
                                            <span class="input-group-text" v-text="(maxCodeLenght - form.code.length)"></span>
                                        </div>
                                    </div>
                                    <form-error :text="form.errors.get('code')" :show="form.errors.has('code')"></form-error>
                                </div>

                                <label for="budget">Budget</label>
                                <div class="form-group row">
                                    <div class="col-4">
                                        <input id="budget" type="number" step=".01" class="form-control" :class="{ 'is-invalid': form.errors.has('budget')}"
                                               name="budget" value="" placeholder="Budget" required v-model="form.budget">
                                        <form-error :text="form.errors.get('budget')" :show="form.errors.has('budget')"></form-error>
                                    </div>

                                    <div class="col-4 ">
                                        <select class="form-control" :class="{ 'is-invalid': form.errors.has('budget_currency')}"
                                                name="budget_currency" id="budget_currency" required v-model="form.budget_currency">
                                            <option value="0" selected disabled>Currency</option>
                                            <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                                                {{ currency.symbol }} {{ currency.name }}
                                            </option>
                                        </select>
                                        <form-error :text="form.errors.get('budget_currency')" :show="form.errors.has('budget_currency')"></form-error>
                                    </div>

                                    <div class="col-4">
                                        <select class="form-control" :class="{ 'is-invalid': form.errors.has('budget_period')}"
                                                name="budget_period" id="budget_period" required v-model="form.budget_period">
                                            <option value="" selected disabled>Budget period</option>
                                            <option v-for="(name, index) in budgetPeriods" :key="index" :value="index">
                                                {{ name }}
                                            </option>
                                        </select>
                                        <form-error :text="form.errors.get('budget_period')" :show="form.errors.has('budget_period')"></form-error>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <card-header>Client</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="">Choose from a list or</label>
                                    <a href="#" data-toggle="modal" data-target="#clientCreate">create new client</a>
                                    <Select2
                                            v-model="form.client"
                                            :options="clientsSelectOptions"
                                            :class="{ 'is-invalid': form.errors.has('client')}"
                                            @change="form.errors.clear('client')"></Select2>
                                </div>
                                <form-error :text="form.errors.get('client')" :show="form.errors.has('client')"></form-error>
                            </div>
                        </div>
                        <button class="mt-3 btn btn-success w-25">Create</button>
                    </div>

                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Members</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2 v-model="form.members"
                                             :options="membersSelectOptions"
                                             :settings="{ multiple: true }"
                                             :class="{ 'is-invalid': form.errors.has('members')}"
                                             @change="form.errors.clear('members')"></Select2>
                                </div>
                                <form-error :text="form.errors.get('members')" :show="form.errors.has('members')"></form-error>
                            </div>
                        </div>

                        <div class="card ">
                            <card-header>Billings</card-header>
                            <div class="card-body">
                                <billings-form v-if="currencies.length > 0 && billingTypes"
                                               :currencies="currencies"
                                               :billingTypes="billingTypes">
                                </billings-form>
                                <hr>
                                <button v-if="!showTasks" class="btn btn-info mb-3" type="button" @click="addTasks">Add tasks</button>
                                <button v-if="showTasks" class="btn btn-info mb-3" type="button" @click="showTasks = false">Remove tasks</button>
                                <tasks-form v-if="(currencies.length > 0 && billingTypes && tasks.length > 0) && showTasks"
                                            :tasks="tasks"
                                            :currencies="currencies"
                                            :billingTypes="billingTypes">
                                </tasks-form>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal -->
            <div class="modal fade" id="clientCreate" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <create-client isModal="true"></create-client>
                        </div>
                    </div>
                </div>
            </div>
            <back-buttton></back-buttton>
        </section>
    </div>
</template>

<script>
    import {Form} from '../../../classes/form.js';
    import String from '../../../classes/string.js';
    import BillingsForm from '../../../components/blocks/billings/Form.vue';
    import TasksForm from '../../../components/blocks/tasks/Form.vue';
    import CreateClient from '../Clients/Create';

    export default {
        components: {
            CreateClient,
            TasksForm,
            BillingsForm,
        },
        data() {
            return {
                showTasks: false,
                maxCodeLenght: 4,
                addProjectTasks: false,
                clients: [],
                members: [],
                currencies: [],
                billingTypes: null,
                budgetPeriods: [],
                tasks: [],
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
            EventHub.listen('client_created', this.clientCreated);
        },

        watch: {
            'form.name': function () {
                let string = new String(this.form.name);
                this.form.code = string.codify(this.maxCodeLenght);
            },
            'form.code': function () {
                let code = this.form.code.toString().toUpperCase();
                this.form.code = code;
            },
            'form.budget_currency': function () {
                this.form.billing_currency = this.form.budget_currency;
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
            }
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.post('/api/projects').then(response => {
                        this.$router.push({name: 'projects.show', params: { id: response.data.id }})
                        this.$awn.success('Created new project');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })
                );
            },

            /**
             * Fallback method listening on client_created event
             */
            clientCreated(data) {
                this.clients.push(data);
                this.form.client = data.id;
                $('#closeDialog').trigger('click');
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
                        this.showTasks = true;
                    }
                });
            },

            /**
             * Load all required data
             */
            fetchData() {
                this.fetchClients();
                this.fetchMembers();
                this.fetchBillingData();
                this.fetchTasks();
            },
            /**
             * Load clients 
             */
            fetchClients() {
                axios.get('/api/clients').then(response => {
                    this.clients = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load members 
             */
            fetchMembers() {
                axios.get('/api/members').then(response => {
                    this.members = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load currencies and budget periods 
             */
            fetchBillingData() {
                axios.get('/api/billings/data').then(response => {
                    this.currencies = response.data.currencies;
                    this.budgetPeriods = response.data.budget_periods;
                    this.billingTypes = response.data.billing_types;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load default tasks for project
             */
            fetchTasks() {
                axios.get('/api/tasks').then(response => {
                    this.tasks = response.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>