<template>
    <section>
<div class="row">
        <div class="col-md-10">
            <h1>{{ member.user.fullname }}</h1>
        </div>
        <div class="col-md-2">
                <router-link
                        v-if="member.id"
                        :to="{ name: 'members.edit', params: { id: member.id } }"
                        class="btn btn-success btn-block ">Edit
                </router-link>  
        </div>
    </div>
    <hr>

    <div class="row mb-3">
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header"><h5><strong>Details</strong></h5></div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tr>
                            <td><strong>First name</strong></td>
                            <td>{{ member.user.firstname }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last name</strong></td>
                            <td>{{ member.user.lastname }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>{{ member.user.email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Active</strong></td>
                            <td>{{ member.active }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card" :class="{ 'border-bottom-0': member.user.roles.length > 0}">
                <div class="card-header"><h5><strong>Roles</strong></h5></div>
                <div class="card-body" :class="{ 'p-0': member.user.roles.length > 0}">
                    <router-link v-for="(role, index) in member.user.roles" :key="role.id" tag="a"
                                 :to="{ name: 'roles.show', params: { id: role.id } }"
                                 class="list-group-item border-right-0 border-left-0"
                                 :class="{ 'border-top-0': index === 0}">
                        {{ role.display_name }}
                    </router-link>  
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header"><h5><strong>Billings</strong></h5></div>
                <div class="card-body">
                     <BillingsShow v-if="member.billing" :billing="member.billing"></BillingsShow>
                </div>
            </div>

            <div v-if="member.projects" class="card" :class="{ 'border-bottom-0': member.projects.length > 0}">
                <div class="card-header"><h5><strong>Projects</strong></h5></div>
                <div class="card-body" :class="{ 'p-0': member.projects.length > 0}">
                    <ul v-if="member.projects.length > 0" class="list-group">
                        <router-link
                                v-for="(project, index) in member.projects"
                                :key="project.id" 
                                :to="/projects/+project.id"
                                class="list-group-item border-right-0 border-left-0"
                                :class="{ 'border-top-0': index === 0}">
                                {{ project.name }} ({{ project.code }})
                        </router-link> 
                    </ul>
                    <span v-else>No projects assigned</span>
                </div>
            </div>
        </div>
    </div>

        <back-buttton class="btn btn-info"></back-buttton>
    </section>
</template>

<script>
    import BillingsShow from '../../../components/blocks/billings/Show.vue';

    export default {
        components: {
            'BillingsShow': BillingsShow
        },
        data() {
            return {
                member: {
                    user: {
                        roles: []
                    },
                    projects: []
                }
            }
        },

        created() {
            axios.get('/api/members/' + this.$route.params.id).then(response => {
                this.member = response.data.data;
            }).catch(error => {
                this.$awn.alert(error.message);
            });
        }
    }
</script>

<style scoped>

</style>