<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Create new member</h1>
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
                                    <label for="firstname">First name</label>
                                    <input id="firstname" type="text" class="form-control" v-model="form.firstname"
                                           name="firstname" value="" placeholder="First name" required>
                                    <form-error :text="form.errors.get('firstname')" :show="form.errors.has('firstname')"></form-error>
                                </div>

                                <div class="form-group">
                                    <label for="lastname">Last name</label>
                                    <input id="lastname" type="text" class="form-control" v-model="form.lastname"
                                           name="lastname" value="" placeholder="Last name" required>
                                    <form-error :text="form.errors.get('lastname')" :show="form.errors.has('lastname')"></form-error>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input id="email" type="email" class="form-control" v-model="form.email"
                                           name="email" placeholder="Email" required>
                                    <form-error :text="form.errors.get('email')" :show="form.errors.has('email')"></form-error>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <card-header>Roles</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2 v-model="form.roles" :options="rolesSelectOptions" :settings="{ multiple: true }"
                                             @change="form.errors.clear('roles')"></Select2>
                                </div>
                                <form-error :text="form.errors.get('roles')" :show="form.errors.has('roles')"></form-error>
                            </div>
                        </div>
                        <button class="mt-3 btn btn-success w-25">Create</button>
                    </div>

                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Billings</card-header>
                            <div class="card-body">
                                <billings-form v-if="currencies.length > 0 && billingTypes"
                                               :currencies="currencies"
                                               :billingTypes="billingTypes">
                                </billings-form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h5><strong>Projects</strong></h5></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2 v-model="form.projects" :options="projectsSelectOptions" :settings="{ multiple: true }"></Select2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</template>

<script>
    import BillingsForm from '../../components/Billings/Form.vue';

    export default {
        components: {
            'BillingsForm': BillingsForm
        },
        data() {
            return {
                roles: [],
                projects: [],
                currencies: [],
                billingTypes: null,
                form: new Form({
                    firstname: '',
                    lastname: '',
                    email: '',
                    roles: [],
                    projects: [],
                    billing_rate: '',
                    billing_type: '',
                    billing_currency: 0
                })
            }
        },

        created() {
            this.fetchData();
        },

        computed: {
            rolesSelectOptions: function () {
                return this.roles.map(function (item) {
                    return {
                        id: item.id,
                        text: item.display_name
                    }
                });
            },

            projectsSelectOptions: function () {
                return this.projects.map(function (item) {
                    return {
                        id: item.id,
                        text: item.name + '(' + item.code + ')'
                    }
                });
            }
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.post('/api/members').then(response => {
                        this.$router.push({name: 'members.show', params: { id: response.data.id }})
                        if (response.warning) {
                            this.$awn.warning(response.warning);
                            return;
                        }
                        this.$awn.success('Created new member');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })
                );
            },

            /**
             * Load data for component
             */
            fetchData() {
                this.fetchRoles();
                this.fetchProjects();
                this.fetchBillingData();
            },

            /**
             * Load roles to use in component
             */
            fetchRoles() {
                axios.get('/api/roles').then(response => {
                    this.roles = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load projects to use in component
             */
            fetchProjects() {
                axios.get('/api/projects').then(response => {
                    this.projects = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load billing data
             */
            fetchBillingData() {
                axios.get('/api/currencies').then(response => {
                    this.currencies = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });

                axios.get('/api/billings/types').then(response => {
                    this.billingTypes = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>