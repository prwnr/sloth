<template>
    <section>
        <div class="card mr-0">
        <div class="card-body">
            <form @submit.prevent="onSubmit"
                    @keydown="form.errors.clear($event.target.name)">
                <div class="form-group">
                    <div class="form-row">
                        <label for="name">Team name</label>
                        <input :class="{disabled : !isAdmin}" :disabled="!isAdmin" id="name" v-model="form.name" type="text"
                                class="form-control"
                                name="name" required autofocus>
                        <span v-show="form.errors.has('name')" class="help-block text-danger"
                                v-html="form.errors.get('name')"></span>
                    </div>
                </div>
                <button v-show="isAdmin" type="submit" class="btn btn-success" :disabled="form.errors.any()">Save</button>
            </form>
        </div>
    </div>
    <div v-if="isAdmin" class="card card mb-3 mt-3">
        <div class="card-header">
            <i class="fa fa-table"></i> Members
        </div>
        <div class="card-body">
            <datatable
                :columns="columns"
                :data="itemsData"
                :total="items.length"
                :query="query"
                :HeaderSettings="false"
                />
        </div>
    </div>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                user: {},
                roles: [],
                items: [],
                teamId: 0,
                form: new Form({
                    name: ''
                }),
                columns: [
                    {title: '#', field: 'id', sortable: true, colStyle: 'width: 50px;'},
                    {title: 'Name', field: 'fullname', sortable: true },
                ],
                query: {sort: 'id', order: 'asc'},
            }
        },

        created() {
            this.fetchData();
        },

        computed: {
            /**
             * Check if user is admin
             */
            isAdmin: function () {
                if (this.roles.includes('admin')) {
                    return true;
                }

                return false;
            },

            itemsData: function () {
                if (this.query.sort) {
                    this.items = _.orderBy(this.items, this.query.sort, this.query.order)
                }

                return this.items.slice(this.query.offset, this.query.offset + this.query.limit)
            }
        },

        watch: {
            /**
             * Load members if isAdmin computed is changed to true
             */
            isAdmin: function () {
                if (this.isAdmin) {
                    this.fetchMembers();
                }
            }
        },

        methods: {
            fetchData() {
                axios.get('/api/users/active').then(response => {
                    this.user = response.data.data;
                    this.roles = response.data.roles;

                    let team = response.data.team;
                    this.teamId = team.id;
                    this.form.name = team.name;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Fetch all members for current Admin
             */
            fetchMembers() {
                axios.get('/api/members').then(response => {
                    this.items = response.data.data;
                    this.items.map(item => {
                        return item.fullname = item.user.fullname;
                    });
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Save team on form submit.
             */
            onSubmit() {
                this.$awn.async(
                    this.form.put('/api/teams/' + this.teamId).then(response => {
                        this.$awn.success('Your team name has been updated.');
                        this.form.updateOriginalData();
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })    
                );
            },
        }
    }
</script>
