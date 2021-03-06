<template>
    <div>
        <div class="card mr-0">
            <div class="card-body">
                <form @submit.prevent="onSubmit" @keydown="form.errors.clear($event.target.name)">
                    <div class="form-group">
                        <div class="form-row">
                            <label for="name">Team name</label>
                            <input autofocus
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   required
                                   type="text"
                                   v-model="form.name">
                            <span class="help-block text-danger"
                                  v-html="form.errors.get('name')"
                                  v-show="form.errors.has('name')">
                        </span>
                        </div>
                    </div>
                    <button :disabled="form.errors.any()"
                            class="btn btn-success"
                            type="submit">
                        Save
                    </button>
                </form>
            </div>
        </div>
        <div class="card card-table">
            <div class="card-header">
                <i class="fa fa-table"></i> Members
            </div>
            <div class="card-body p-0">
                <datatable
                        :columns="columns"
                        :data="itemsData"
                        :total="items.length"
                        :query="query"
                        :HeaderSettings="false"
                />
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ProfileTeam',
        props: {
            team: {
                type: Object,
                required: true
            }
        },

        data() {
            return {
                items: [],
                teamId: this.team.id,
                form: new Form({
                    name: this.team.name
                }),
                columns: [
                    {title: '#', field: 'id', sortable: true, colStyle: 'width: 70px;'},
                    {title: 'Name', field: 'fullname', sortable: true},
                    {title: 'Email', field: 'email', sortable: true},
                    {title: 'Active?', field: 'active', sortable: true},
                ],
                query: {sort: 'id', order: 'asc'},
            }
        },

        created() {
            this.fetchMembers();
        },

        computed: {
            itemsData: function () {
                if (this.query.sort) {
                    this.items = _.orderBy(this.items, this.query.sort, this.query.order)
                }

                return this.items.slice(this.query.offset, this.query.offset + this.query.limit)
            }
        },

        methods: {
            /**
             * Fetch all members for current Admin
             */
            fetchMembers() {
                axios.get('members').then(response => {
                    this.items = response.data.data;
                    this.items.map(item => {
                        item.fullname = item.user.fullname;
                        item.email = item.user.email;
                        return item;
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
                    this.form.put('teams/' + this.teamId).then(response => {
                        this.$emit('team-updated', response.data);
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

<style scoped>
    .card-table {
        margin-bottom: 1rem !important;
    }
</style>
