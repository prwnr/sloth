<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Project: {{ project.name }}</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                v-if="project.id"
                                :to="{ name: 'projects.edit', params: { id: project.id } }"
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
                        <card-header>Details</card-header>
                        <div class="card-body">
                            <table class="table table-striped mb-0">
                                <tr>
                                    <td><strong>Name</strong></td>
                                    <td>{{ project.name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Short code</strong></td>
                                    <td>{{ project.code }}</td>
                                </tr>
                                <tr v-if="project.budget_currency">
                                    <td><strong>Budget</strong></td>
                                    <td><strong>{{ project.budget }} {{ project.budget_currency.name }}</strong>
                                        ({{ project.budget_currency.symbol }}) - {{ project.budget_period }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Client</strong></td>
                                    <td>
                                        <router-link
                                                v-if="project.client"
                                                tag="a"
                                                :to="{ path: '/clients/' + project.client.id }"
                                                class="">{{ project.client.company_name }}
                                        </router-link>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card card mb-3 mt-3" :class="{ 'border-bottom-0': project.members.length > 0}">
                        <card-header>Assigned members</card-header>
                        <div class="card-body" :class="{ 'p-0': project.members.length > 0}">
                            <ul v-if="project.members.length > 0" class="list-group">
                                <router-link
                                        v-for="(member, index) in project.members"
                                        :key="member.id"
                                        :to="{ name: 'members.show', params: { id: member.id } }"
                                        class="list-group-item border-right-0 border-left-0"
                                        :class="{ 'border-top-0': index === 0}">
                                    {{ member.user.fullname }}
                                </router-link>
                            </ul>
                            <span v-else>No members assigned</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-3">
                        <card-header>Billings</card-header>
                        <div class="card-body">
                            <billings-show v-if="project.billing" :billing="project.billing"></billings-show>

                            <hr v-if="project.tasks.length > 0">
                            <tasks-show v-if="project.tasks.length > 0" :tasks="project.tasks"></tasks-show>
                        </div>
                    </div>
                </div>
            </div>

            <back-button></back-button>
        </section>
    </div>
</template>

<script>
    import BillingsShow from '../../components/Billings/Show.vue';
    import TasksShow from '../../components/Tasks/Show.vue';

    export default {
        components: {
            BillingsShow,
            TasksShow
        },
        data() {
            return {
                project: {
                    members: [],
                    client: {},
                    tasks: []
                }
            }
        },

        created() {
            this.fetchData();
        },

        methods: {
            /**
             * Load data for component
             */
            fetchData() {
                axios.get('/api/projects/' + this.$route.params.id).then(response => {
                    this.project = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }
    }
</script>

<style scoped>

</style>