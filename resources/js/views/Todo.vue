<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>Personal to do list</h1>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success btn-block" type="button" data-toggle="modal"
                                data-target="#new">Add new <i class="fa fa-fw fa-plus"></i></button>
                    </div>
                </div>
            </div>
        </section>
        <section class="content pb-5">
            <div class="card mb-4">
                <div>
                    <div class="p-3" v-if="items.length == 0">Too slothful to make a list?</div>
                    <div class="list-group list-group-flush">
                        <todo-item v-for="item in items" :key="item.id" :item="item" @item-deleted="deleteItem"></todo-item>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="new" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content card-primary card-outline">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <new-todo :projects="projects" @item-created="addItem"></new-todo>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import TodoItem from '../components/Todos/Item';
    import NewTodo from '../components/Todos/NewItem';

    export default {
        name: "Todo",

        components: {
            TodoItem,
            NewTodo
        },

        data() {
            return {
                loading: false,
                items: [],
                projects: []
            }
        },

        created() {
            this.fetchData();
        },

        methods: {
            /**
             * Add item to the list
             * @param item
             */
            addItem(item) {
                this.items.push(item)
            },

            /**
             * Delete item from the list
             * @param id
             */
            deleteItem(id) {
                axios.delete(`/api/todos/${id}`).then(response => {
                    this.items = this.items.filter(item => {
                        if (item.id !== id) {
                            return true;
                        }
                    });
                    this.$awn.success('Todo task succesfully deleted.');
                }).catch(error => {
                    this.$awn.alert(error.message);
                })
            },

            /**
             * Fetch requried data
             */
            fetchData() {
                this.loading = true;
                axios.get('/api/todos').then(response => {
                    let items = response.data.data;
                    items = items.map(item => {
                        item.project_name = item.project.name;
                        return item;
                    })

                    this.items = items;
                }).catch(error => {
                    this.$awn.alert(error.message);
                }).finally(() => {
                    this.loading = false;
                })

                axios.get('/api/projects').then(response => {
                    this.projects = response.data.data;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        },
    }
</script>

<style scoped>
    .modal-dialog {
        margin-top: 5% !important;
    }
</style>