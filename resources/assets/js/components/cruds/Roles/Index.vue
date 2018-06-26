<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Roles</h1>
                    </div>
                    <div class="col-sm-2">
                        <router-link :to="{ name: 'roles.create' }" class="btn btn-success btn-block">Create new</router-link>

                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="card card-table">
                <card-header>Roles list</card-header>
                <div class="card-body">
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
    import DatatableActions from '../../dataTable/Actions'

    export default {
        data() {
            return {
                loading: true,
                items: [],
                columns: [
                    {title: '#', field: 'id', sortable: true, colStyle: 'width: 50px;'},
                    {title: 'Name', field: 'display_name', sortable: true},
                    {title: 'Description', field: 'description', sortable: true},
                    {title: 'Created at', field: 'created_at', sortable: true},
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
                    route: 'roles',
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
                axios.get('/api/roles').then(response => {
                    this.items = response.data.data
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
                    axios.delete('/api/roles/' + id).then(response => {
                        this.items = this.items.filter((item) => {
                            return item.id != id
                        });

                        this.$awn.success('Role succesfully deleted');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })
                );
            },
        }
    }
</script>