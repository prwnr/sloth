<template>
    <div class="mb-2 d-inline">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            Filters
        </button>
        <ul class="dropdown-menu dropdown-menu-right dropdown-filters" x-placement="top-start">
            <form id="filters" class="px-4 py-3">
                <div class="row">
                    <div v-if="!disabled('members')" class="form-group" :class="[disabled('clients') ? 'col-12' : 'col-6']">
                        <label>Members</label>
                        <Select2 v-model="applied.members"
                                 :options="membersOptions"
                                 :settings="{ multiple: true }"
                        ></Select2>
                    </div>
                    <div v-if="!disabled('clients')" class="form-group" :class="[disabled('members') ? 'col-12' : 'col-6']">
                        <label>Clients</label>
                        <Select2 v-model="applied.clients"
                                 :options="clientsOptions"
                                 :settings="{ multiple: true }"
                        ></Select2>
                    </div>
                </div>
                <div v-if="!disabled('projects')" class="form-group">
                    <label>Projects</label>
                    <Select2 v-model="applied.projects"
                             :options="projectsOptions"
                             :settings="{ multiple: true }"
                    ></Select2>
                </div>
                <div v-if="!disabled('billable')" class="form-group">
                    <label>Bilable?</label>
                    <div class="form-check">
                        <input type="checkbox" v-model="applied.billable" :disabled="shouldDisableBillabe('yes')" value="yes" class="form-check-input" id="billableCheck">
                        <label class="form-check-label" for="billableCheck">
                            Yes
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" v-model="applied.billable" :disabled="shouldDisableBillabe('no')" value="no" class="form-check-input" id="nonBillableCheck">
                        <label class="form-check-label" for="nonBillableCheck">
                            No
                        </label>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" @click="reset">Reset</button>
                    <button type="button" class="btn btn-success pr-2" @click="apply">Apply</button>
                </div>
            </form>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            disableFilters: {
                type: Array,
                default: () => []
            }
        },

        data() {
            return {
                members: [],
                clients: [],
                projects: [],
                billable: ['yes', 'no'],
                applied: {
                    members: [],
                    clients: [],
                    projects: [],
                    billable: ['yes', 'no']
                },
            }
        },

        created() {
            this.fetchData();
            //Disable dropdown close on clikc inside dropdown menu
            $(document).on('click', '.dropdown-filters, .select2-selection__choice__remove', function (e) {
                e.stopPropagation();
            });
        },

        computed: {
            membersOptions: function () {
                return this.members.map(function (item) {
                    return {
                        id: item.id,
                        text: item.fullname
                    }
                });
            },
            projectsOptions: function () {
                return this.projects.map(function (item) {
                    return {
                        id: item.id,
                        text: item.name + ' (' + item.code + ')'
                    }
                });
            },
            clientsOptions: function () {
                return this.clients.map(function (item) {
                    return {
                        id: item.id,
                        text: item.company_name
                    }
                });
            }
        },

        methods: {
            /**
             * Emits event with applied filters
             */
            apply() {
                $('.filters .dropdown-toggle').click();

                this.$emit('applied', this.applied);
            },

            /**
             * Reset applied filters and emits event
             */
            reset() {
                this.applied = {
                    members: [],
                    clients: [],
                    projects: [],
                    billable: this.billable
                };

                this.$emit('applied', this.applied);
            },

            /**
             * Check if billable checkbox should be disabled
             * Prevents both checkboxes from being unchecked
             */
            shouldDisableBillabe(value) {
                return this.applied.billable.length == 1 && this.applied.billable.includes(value);
            },

            /**
             * Check if specific filter should be disabled
             */
            disabled(filter) {
                if (this.disableFilters.includes(filter)) {
                    return true;
                }

                return false;
            },

            /**
             * Fetch data to filters
             */
            fetchData() {
                this.fetchClients();
                this.fetchMembers();
                this.fetchProjects();
            },

            /**
             * Load clients
             */
            fetchClients() {
                axios.get('/api/clients').then(response => {
                    this.clients = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load members
             */
            fetchMembers() {
                axios.get('/api/users').then(response => {
                    this.members = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load projects
             */
            fetchProjects() {
                axios.get('/api/projects').then(response => {
                    this.projects = response.data.data
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }
    }
</script>

<style scoped>
    .dropdown-filters {
        min-width: 580px;
        max-width: 600px;
    }
</style>