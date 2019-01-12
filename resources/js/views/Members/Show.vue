<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>{{ member.user.fullname }}</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                v-if="member.id && member.editable"
                                :to="{ name: 'members.edit', params: { id: member.id } }"
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
                            <table class="table table-card table-striped">
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
                            </table>
                        </div>
                    </div>

                    <report-view v-if="reportItems" :items="reportItems"></report-view>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-3">
                        <card-header>Billings</card-header>
                        <div class="card-body">
                            <BillingsShow v-if="member.billing" :billing="member.billing"></BillingsShow>
                        </div>
                    </div>

                    <div class="card" :class="{ 'border-bottom-0': member.roles.length > 0}">
                        <card-header>Roles</card-header>
                        <div class="card-body" :class="{ 'p-0': member.roles.length > 0}">
                            <router-link v-for="(role, index) in member.roles" :key="role.id" tag="a"
                                         :to="{ name: 'roles.show', params: { id: role.id } }"
                                         class="list-group-item border-right-0 border-left-0"
                                         :class="{ 'border-top-0': index === 0}">
                                {{ role.display_name }}
                            </router-link>
                        </div>
                    </div>

                    <div v-if="member.projects" class="card" :class="{ 'border-bottom-0': member.projects.length > 0}">
                        <card-header>Projects</card-header>
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
        </section>
    </div>
</template>

<script>
    import BillingsShow from '../../components/Billings/Show.vue';
    import ReportView from '../../components/Report/ReportView.vue';

    export default {
        components: {
            BillingsShow,
            ReportView
        },
        data() {
            return {
                member: {
                    user: {},
                    roles: [],
                    projects: []
                },
                reportItems: null
            }
        },

        created() {
            axios.get('/api/members/' + this.$route.params.id).then(response => {
                this.member = response.data.data;
                let report = response.data.report;
                this.reportItems = [
                    { title: 'Total hours', value: report.total_hours},
                    { title: 'Total billable hours', value: report.total_billable_hours},
                    { title: 'Total sale', value: report.total_sale},
                    { title: 'Total earnings', value: report.total_earnings}
                ];
            }).catch(error => {
                this.$awn.alert(error.message);
            });
        }
    }
</script>

<style scoped>

</style>