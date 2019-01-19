<template>
    <div class="mb-2 d-inline">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            Filters
        </button>
        <ul class="dropdown-menu dropdown-menu-right dropdown-filters" x-placement="top-start">
            <form id="filters" class="px-4 py-3">
                <div class="row">
                    <div :class="[disabled('clients') ? 'col-12' : 'col-6']"
                         class="form-group"
                         v-if="!disabled('members')">

                        <label>Members</label>
                        <Select2
                                v-model="applied.members"
                                :options="membersOptions"
                                :settings="{ multiple: true }">
                        </Select2>
                    </div>
                    <div :class="[disabled('members') ? 'col-12' : 'col-6']"
                         class="form-group"
                         v-if="!disabled('clients')">

                        <label>Clients</label>
                        <Select2
                                v-model="applied.clients"
                                :options="clientsOptions"
                                :settings="{ multiple: true }">
                        </Select2>
                    </div>
                </div>
                <div class="form-group"
                     v-if="!disabled('projects')">

                    <label>Projects</label>
                    <Select2
                            v-model="applied.projects"
                            :options="projectsOptions"
                            :settings="{ multiple: true }">
                    </Select2>
                </div>
                <div class="row">
                    <div class="form-group col-lg-6"
                         v-if="!disabled('billable')">

                        <label>Bilable?</label>
                        <div class="form-check">
                            <input :disabled="shouldDisableBillabe('yes')"
                                   class="form-check-input"
                                   id="billableCheck"
                                   type="checkbox"
                                   v-model="applied.billable"
                                   value="yes">
                            <label class="form-check-label" for="billableCheck">
                                Yes
                            </label>
                        </div>
                        <div class="form-check">
                            <input :disabled="shouldDisableBillabe('no')"
                                   class="form-check-input"
                                   id="nonBillableCheck"
                                   type="checkbox"
                                   v-model="applied.billable"
                                   value="no">
                            <label class="form-check-label" for="nonBillableCheck">
                                No
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-lg-6"
                         v-if="!disabled('status')">

                        <label>Status</label>
                        <select class="form-control"
                                id="status"
                                name="status"
                                v-model="applied.status">
                            <option
                                    :key="index"
                                    :value="index"
                                    v-for="(status, index) in statuses">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <div class="text-right">
                    <button @click="reset"
                            class="btn btn-secondary"
                            type="button">
                        Reset
                    </button>
                    <button @click="apply"
                            class="btn btn-success pr-2"
                            type="button">
                        Apply
                    </button>
                </div>
            </form>
        </ul>
    </div>
</template>

<script>
    export default {
        name: 'ReportFilters',
        props: {
            disableFilters: {
                type: Array,
                required: false,
                default: () => []
            }
        },

        data() {
            return {
                members: [],
                clients: [],
                projects: [],
                billable: ['yes', 'no'],
                statuses: {
                    1: 'Finished',
                    2: 'In progress',
                    3: 'All'
                },
                applied: {
                    members: [],
                    clients: [],
                    projects: [],
                    billable: ['yes', 'no'],
                    status: 3
                },
            }
        },

        created() {
            this.fetchData();
            //Disable dropdown close on click inside dropdown menu
            $(document).on('click', '.dropdown-filters, .select2-selection__choice__remove', function (e) {
                e.stopPropagation();
            });
        },

        computed: {
            membersOptions: function () {
                return this.members.map(function (item) {
                    return {
                        id: item.id,
                        text: item.user.fullname
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
                    billable: this.billable,
                    status: 3
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
                axios.get('clients').then(response => {
                    this.clients = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load members
             */
            fetchMembers() {
                axios.get('members').then(response => {
                    this.members = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Load projects
             */
            fetchProjects() {
                axios.get('projects').then(response => {
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