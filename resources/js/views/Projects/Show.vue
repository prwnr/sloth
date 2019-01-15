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
                        <div class="card-body p-0">
                            <table class="table table-card table-striped mb-0">
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

                    <report-view v-if="reportItems" :items="reportItems" :budget="reportBudget"></report-view>

                </div>

                <div class="col-lg-6">
                    <div class="card mb-3">
                        <card-header>Assigned members</card-header>
                        <ul v-if="project.members.length > 0" class="list-group list-group-flush">
                            <router-link
                                    v-for="member in project.members"
                                    :key="member.id"
                                    :to="{ name: 'members.show', params: { id: member.id } }"
                                    class="list-group-item">
                                {{ member.user.fullname }}
                            </router-link>
                        </ul>
                        <span v-else class="pl-3 pr-3 pt-2 pb-2">No members assigned</span>
                    </div>

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
        </section>
    </div>
</template>

<script>
    import BillingsShow from '../../components/Billings/Show.vue';
    import TasksShow from '../../components/Tasks/Show.vue';
    import ReportView from '../../components/Report/ReportView.vue';

    export default {
        name: 'ProjectsShow',
        components: {
            BillingsShow,
            TasksShow,
            ReportView
        },
        data() {
            return {
                project: {
                    members: [],
                    client: {},
                    tasks: []
                },
                reportItems: null,
                reportBudget: null
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
                axios.get('projects/' + this.$route.params.id).then(response => {
                    this.project = response.data.data;
                    let report = response.data.report;
                    this.reportItems = [
                        { title: 'Total hours', value: report.total_hours},
                        { title: 'Total billable hours', value: report.total_billable_hours},
                        { title: 'Total sales', value: report.total_sale}
                    ];
                    this.reportBudget = {
                        sale: report.total_sale,
                        total: this.project.budget
                    };
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }
    }
</script>

<style scoped>

</style>