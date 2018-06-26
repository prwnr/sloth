<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>{{ form.firstname }} {{ form.lastname }} member</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                exact
                                v-if="member.id"
                                :to="{ name: 'members.show', params: { id: member.id } }"
                                class="btn btn-info btn-block">View
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
                        <button class="mt-3 btn btn-success w-25">Save</button>
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
                            <card-header>Projects</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2 v-model="form.projects" :options="projectsSelectOptions" :settings="{ multiple: true }"></Select2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <hr>
            <div class="row">
                <div class="col-lg-6">
                    <form @submit.prevent="changePassword" @keydown="formPassword.errors.clear($event.target.name)">
                        <div class="card mt-3">
                            <card-header>Member password</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input id="password" v-model="formPassword.password" type="password" required
                                           class="form-control" autocomplete="off"
                                           name="password">
                                    <span class="help-block text-danger"
                                          v-html="formPassword.errors.get('password')" v-show="formPassword.errors.has('password')"></span>
                                </div>
                                <div class="form-group">
                                    <label for="password-confirm">Confirm password</label>
                                    <input id="password-confirm" v-model="formPassword.password_confirmation" type="password" required
                                           class="form-control" autocomplete="off"
                                           name="password_confirmation">
                                    <span class="help-block text-danger"
                                          v-html="formPassword.errors.get('password_confirmation')"
                                          v-show="formPassword.errors.has('password_confirmation')"></span>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-success">Change password</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <back-button></back-button>
        </section>
    </div>
</template>

<script>
    import {Form} from '../../../classes/form.js';
    import BillingsForm from '../../../components/blocks/billings/Form.vue';

    export default {
        components: {
            BillingsForm
        },
        data() {
            return {
                member: {},
                roles: [],
                projects: [],
                currencies: [],
                billingTypes: [],
                form: new Form({
                    firstname: '',
                    lastname: '',
                    email: '',
                    roles: [],
                    projects: [],
                    billing_rate: '',
                    billing_type: '',
                    billing_currency: 0
                }),
                formPassword: new Form({
                    password: '',
                    password_confirmation: ''
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
                        text: item.name + ' (' + item.code + ')'
                    }
                });
            }
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.put('/api/members/' + this.$route.params.id).then(response => {
                        this.$awn.success('Member updated successfully.');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })    
                );
            },
            
            /**
             * Password change call
             */
            changePassword() {
                this.$swal({
                    title: 'Are you sure?',
                    text: 'You won\'t be able to revert password change to the previous one!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    confirmButtonColor: '#28a745',
                    focusCancel: true,
                    reverseButtons: true
                }).then(result => {
                    if (result.value) {
                        this.$awn.async(
                            this.formPassword.put('/api/users/' + this.member.user.id + '/password').then(response => {
                                this.$awn.success('Member password updated successfully.');
                            }).catch(error => {
                                this.$awn.alert(error.message);
                            })
                        );
                    }
                });
            },

            /**
             * Load data for component
             */
            fetchData() {
                this.fetchMember();
                this.fetchRoles();
                this.fetchProjects();
                this.fetchBillingData();
            },

            /**
             * Load member data
             */
            fetchMember() {
                axios.get('/api/members/' + this.$route.params.id).then(response => {
                    this.member = response.data.data;
                    this.form.firstname = this.member.user.firstname;
                    this.form.lastname = this.member.user.lastname;
                    this.form.email = this.member.user.email;
                    this.form.roles = this.member.user.roles.map(item => item.id);
                    this.form.projects = this.member.projects.map(item => item.id);
                    this.form.billing_rate = this.member.billing.rate;
                    this.form.billing_type = this.member.billing.type;
                    this.form.billing_currency = this.member.billing.currency.id;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load roles
             */
            fetchRoles() {
                axios.get('/api/roles').then(response => {
                    this.roles = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load projects
             */
            fetchProjects() {
                axios.get('/api/projects').then(response => {
                    this.projects = response.data.data;
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