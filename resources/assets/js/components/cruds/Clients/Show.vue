<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>{{ client.fullname }} - {{ client.company_name }}</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                v-if="client.id"
                                :to="{ name: 'clients.edit', params: { id: client.id } }"
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
                        <card-header>Company Details</card-header>
                        <div class="card-body">
                            <table class="table table-striped">
                                <tr>
                                    <td><strong>Company Name</strong></td>
                                    <td>{{ client.company_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Company Address</strong></td>
                                    <td>{{ client.street }}, {{ client.zip }}, {{ client.city }}, {{ client.country }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>VAT/TAX</strong></td>
                                    <td>{{ client.vat }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Person</strong></td>
                                    <td>{{ client.fullname }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Email</strong></td>
                                    <td>{{ client.email }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card" :class="{ 'mb-3 mt-3 border-bottom-0' : client.projects.length > 0}">
                        <card-header>Projects</card-header>
                        <div class="card-body" :class="{ 'p-0' : client.projects.length > 0}">
                            <ul v-if="client.projects.length > 0" class="list-group">
                                <router-link
                                        v-for="(project, index) in client.projects"
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

                <div class="col-lg-6">
                    <div class="card mb-3">
                        <card-header>Contact Details</card-header>
                        <div class="card-body">
                            <BillingsShow v-if="client.billing" :billing="client.billing"></BillingsShow>
                        </div>
                    </div>
                </div>
            </div>

            <back-button></back-button>
        </section>
    </div>
</template>

<script>
    import BillingsShow from '../../../components/blocks/billings/Show.vue';

    export default {
        components: {
            'BillingsShow': BillingsShow
        },

        data() {
            return {
                client: {
                    projects: []
                }
            }
        },

        created() {
            axios.get('/api/clients/' + this.$route.params.id).then(response => {
                this.client = response.data.data;
            }).catch(error => {
                this.$awn.alert(error.message);
            });
        }
    }
</script>

<style scoped>

</style>