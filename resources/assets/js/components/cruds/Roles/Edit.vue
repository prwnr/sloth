<template>
    <section>
        <div class="row">
            <div class="col-md-10">
                <h1>{{ form.display_name }} role</h1>
            </div>
            <div class="col-md-2">
                <router-link
                        exact
                        v-if="role.editable"
                        :to="{ name: 'roles.show', params: { id: role.id } }"
                        class="btn btn-info btn-block ">View
                </router-link>
            </div>
        </div>
        <hr>

        <form @submit.prevent="submitForm" @keydown="form.errors.clear($event.target.name)">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header"><h5><strong>Information</strong></h5></div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="display_name">Name</label>
                                <input id="display_name" type="text" class="form-control"
                                       name="display_name" placeholder="Name" v-model="form.display_name"
                                       @keydown="form.errors.clear('name')">
                                <form-error :text="form.errors.get('display_name')" :show="form.errors.has('display_name')"></form-error>
                                <form-error :text="form.errors.get('name')" :show="form.errors.has('name')"></form-error>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" class="form-control"
                                          name="description" value="" placeholder="Description" v-model="form.description"></textarea>
                                <form-error :text="form.errors.get('description')" :show="form.errors.has('description')"></form-error>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header "><h5><strong>Assigned users</strong></h5></div>
                        <div class="card-body">
                            <div class="form-group">
                                <Select2 v-model="form.users" :options="usersSelectOptions" :settings="{ multiple: true }"></Select2>
                            </div>
                        </div>
                    </div>
                    <button class="mt-3 btn btn-success w-25" type="button" @click="submitForm">Save</button>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h5><strong>Permissions</strong></h5></div>
                        <div class="card-body">

                            <div v-for="permission in permissions" class="form-check" :key="permission.id">
                                <input class="form-check-input" type="checkbox" v-model="form.permissions"
                                       :value="permission.id">
                                <label class="form-check-label">
                                    {{ permission.display_name }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <back-buttton class="btn btn-info"></back-buttton>
    </section>
</template>

<script>
    import {Form} from '../../../classes/form.js';

    export default {
        data() {
            return {
                role: {},
                users: [],
                permissions: [],
                form: new Form({
                    display_name: '',
                    description: '',
                    users: [],
                    permissions: [],
                })
            }
        },

        created() {
            this.fetchData();
        },

        computed: {
            usersSelectOptions: function () {
                return this.users.map(function (item) {
                    return {
                        id: item.id,
                        text: item.fullname
                    }
                });
            }
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.put('/api/roles/' + this.$route.params.id).then(response => {
                        this.$awn.success('Role updated successfully.');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })    
                );
            },

            /**
             * Load data for component
             */
            fetchData() {
                this.fetchRole();
                this.fetchUsers();
                this.fetchPerms();
            },

            /**
             * Load current role
             */
            fetchRole() {
                axios.get('/api/roles/' + this.$route.params.id).then(response => {
                    this.role = response.data.data;
                    this.form.display_name = this.role.display_name;
                    this.form.description = this.role.description;
                    this.form.users = this.role.users.map(item => item.id);
                    this.form.permissions = this.role.perms.map(item => item.id);
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load users to use in component
             */
            fetchUsers() {
                axios.get('/api/users').then(response => {
                    this.users = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load Permissions to use in component
             */
            fetchPerms() {
                axios.get('/api/perms').then(response => {
                    this.permissions = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }
    }
</script>

<style scoped>

</style>