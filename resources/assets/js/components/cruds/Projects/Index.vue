<template>
    <section>
        <div class="row">
            <div class="col-md-10">
                <h1>Projects</h1>
            </div>
            <div class="col-md-2">
                <router-link :to="{ name: 'projects.create' }" class="btn btn-success btn-block">Create new</router-link>
            </div>
        </div>
        <hr>

        <div class="card card mb-3">
            <div class="card-header">
                <i class="fa fa-table"></i> Projects list
            </div>
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
                    {title: 'Name', field: 'name', sortable: true},
                    {title: 'Short code', field: 'code', sortable: true},
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
                    route: 'projects',
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
                axios.get('/api/projects').then(response => {
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
                    axios.delete('/api/projects/' + id).then(response => {
                        this.items = this.items.filter((item) => {
                            return item.id != id
                        });

                        this.$awn.success('Project succesfully deleted.');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })    
                );
            },
        }
    }
</script>