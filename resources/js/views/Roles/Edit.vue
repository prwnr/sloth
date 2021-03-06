<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
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
            </div>
        </section>
        <section class="content">
            <form @submit.prevent="submitForm" @keydown="form.errors.clear($event.target.name)">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Information</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="display_name">Name</label>
                                    <input id="display_name"
                                           type="text"
                                           class="form-control"
                                           name="display_name"
                                           placeholder="Name"
                                           v-model="form.display_name"
                                           @keydown="form.errors.clear('name')">
                                    <form-error
                                            :text="form.errors.get('display_name')"
                                            :show="form.errors.has('display_name')">
                                    </form-error>
                                    <form-error
                                            :text="form.errors.get('name')"
                                            :show="form.errors.has('name')">
                                    </form-error>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description"
                                              class="form-control"
                                              name="description"
                                              placeholder="Description"
                                              v-model="form.description">
                                    </textarea>
                                    <form-error
                                            :text="form.errors.get('description')"
                                            :show="form.errors.has('description')">
                                    </form-error>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <card-header>Assigned members</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <Select2
                                            v-model="form.members"
                                            :options="membersSelectOptions"
                                            :settings="{ multiple: true }">
                                    </Select2>
                                </div>
                            </div>
                        </div>
                        <button
                                class="mt-3 btn btn-success w-25"
                                type="button"
                                @click="submitForm">
                            Save
                        </button>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <card-header>Permissions</card-header>
                            <div class="card-body">
                                <div :key="permission.id"
                                     class="form-check"
                                     v-for="permission in permissions">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           v-model="form.permissions"
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
        </section>
    </div>
</template>

<script>
    export default {
        name: 'RolesEdit',
        data() {
            return {
                role: {},
                members: [],
                permissions: [],
                form: new Form({
                    display_name: '',
                    description: '',
                    members: [],
                    permissions: [],
                })
            }
        },

        created() {
            this.fetchData();
        },

        computed: {
            membersSelectOptions: function () {
                return this.members.map(function (item) {
                    return {
                        id: item.id,
                        text: item.user.fullname
                    }
                });
            }
        },

        watch: {
            role: function () {
                if (!this.role.editable) {
                    this.$awn.alert('You are not allowed to edit this role')
                    this.$router.push({ name: 'roles.index'});
                    return;
                }
            }
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.put('roles/' + this.$route.params.id).then(response => {
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
                this.fetchMembers();
                this.fetchPerms();
            },

            /**
             * Load current role
             */
            fetchRole() {
                axios.get('roles/' + this.$route.params.id).then(response => {
                    this.role = response.data.data;
                    this.form.display_name = this.role.display_name;
                    this.form.description = this.role.description;
                    this.form.members = this.role.members.map(item => item.id);
                    this.form.permissions = this.role.perms.map(item => item.id);
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load users to use in component
             */
            fetchMembers() {
                axios.get('members').then(response => {
                    this.members = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load Permissions to use in component
             */
            fetchPerms() {
                axios.get('perms').then(response => {
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