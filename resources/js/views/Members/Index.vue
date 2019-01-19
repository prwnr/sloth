<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Team members</h1>
                    </div>
                    <div class="col-sm-2">
                        <router-link
                                :to="{ name: 'members.create' }"
                                class="btn btn-success btn-block">
                            Create new
                        </router-link>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="card card-table">
                <card-header :minimizable="false">Members list</card-header>
                <div class="card-body p-0">
                    <loading v-if="loading"></loading>

                    <datatable
                            v-if="!loading"
                            :columns="columns"
                            :data="itemsData"
                            :total="items.length"
                            :query="query"
                            :xprops="xprops"
                            :HeaderSettings="false"
                    />
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import DatatableActions from '../../components/DataTable/Actions'

    export default {
        name: 'MembersIndex',
        data() {
            return {
                loading: true,
                items: [],
                columns: [
                    {title: '#', field: 'id', sortable: true, colStyle: 'width: 70px;'},
                    {title: 'Name', field: 'fullname', sortable: true},
                    {title: 'Email', field: 'email', sortable: true},
                    {title: 'Roles', field: 'role', sortable: true},
                    {title: 'Updated at', field: 'updated_at', sortable: true},
                    {
                        title: 'Actions',
                        tdComp: DatatableActions,
                        thClass: 'text-right',
                        tdClass: 'text-right',
                        colStyle: 'width: 130px;'
                    }
                ],
                query: {sort: 'id', order: 'asc'},
                xprops: {
                    route: 'members',
                    destroy: (id) => this.destroyData(id)
                }
            }
        },

        created() {
            this.fetchData();
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
             * Get data from API
             */
            fetchData() {
                this.loading = true;
                axios.get('members').then(response => {
                    this.items = response.data.data
                    this.items.map(item => {
                        item.fullname = item.user.fullname;
                        item.email = item.user.email;
                        item.role = item.roles.map(item => {
                            return item.display_name
                        }).join(', ');
                        return item;
                    });
                    this.loading = false;
                }).catch(error => {
                    this.$awn.alert(error.message);
                }).finally(() => {
                    this.loading = false;
                });
            },

            /**
             * Destroy Role by given ID
             * @param id
             */
            destroyData(id) {
                this.$awn.async(
                    axios.delete('members/' + id).then(response => {
                        this.items = this.items.filter((item) => {
                            return item.id != id
                        });

                        this.$awn.success('Member succesfully deleted.');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })    
                );
            },
        }
    }
</script>