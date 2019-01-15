<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>{{ role.display_name }} role</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                v-if="role.editable"
                                :to="{ name: 'roles.edit', params: { id: role.id } }"
                                class="btn btn-success btn-block ">Edit
                        </router-link>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <card-header>Information</card-header>
                        <div class="card-body p-0">
                            <table class="table table-card table-striped mb-0">
                                <tr>
                                    <td><strong>Code name</strong></td>
                                    <td>{{ role.name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Display name</strong></td>
                                    <td>{{ role.display_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Description</strong></td>
                                    <td>{{ role.description }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div v-if="role.members" class="card" :class="{ 'border-bottom-0': role.members.length > 0}">
                        <card-header>Assigned members</card-header>
                        <div class="card-body" :class="{ 'p-0': role.members.length > 0}">
                            <ul v-if="role.members.length > 0" class="list-group">
                                <router-link
                                        v-if="member"
                                        v-for="(member, index) in role.members"
                                        :key="member.id"
                                        :to="{ name: 'members.show', params: { id: member.id } }"
                                        class="list-group-item border-right-0 border-left-0"
                                        :class="{ 'border-top-0': index === 0}">
                                    {{ member.user.fullname }}
                                </router-link>
                                <span v-else class="list-group-item border-right-0 border-left-0" :class="{ 'border-top-0': index === 0}">{{ member.user.fullname }}</span>
                            </ul>
                            <span v-if="role.members.length == 0">No members assigned</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-bottom-0">
                        <card-header>Permissions</card-header>
                        <div class="card-body p-0">
                            <ul class="list-group">
                                <li v-for="(permission, index) in permissions" :key="permission.id" class="list-group-item border-right-0 border-left-0 @endif"
                                    :class="[hasPermission(permission) ? 'text-success' : 'disabled', {'border-top-0' : index == 0} ]">
                                    {{ permission.display_name }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    export default {
        name: 'RolesShow',
        data() {
            return {
                role: {},
                permissions: [],
            }
        },

        created() {
            this.fetchData(this.$route.params.id);
        },

        methods: {
            fetchData(id) {
                axios.get('roles/' + id).then(response => {
                    this.role = response.data.data;
                }).catch(error => {
                    let message = error.message;
                    if (error.response.status == 404) {
                        message = 'Role not found';
                        this.$router.push({ name: 'roles.index' });
                    }
                    this.$awn.alert(message);
                });

                axios.get('perms').then(response => {
                    this.permissions = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Find if current Role has given permission
             * @param permission
             */
            hasPermission(permission) {
                if (!this.role.perms) {
                    return false;
                }

                return this.role.perms.find(item => item.id === permission.id);
            }
        }
    }
</script>

<style scoped>

</style>