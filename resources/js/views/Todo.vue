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
                        <task v-for="item in items" :key="item.id" :item="item" @task-deleted="deleteTask" @updating-task="handleTaskUpdate"></task>
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
                            <new-task :projects="projects" @task-created="createTask"></new-task>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content card-primary card-outline">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="closeEditDialog">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <edit-task v-if="editedTask"
                                       :projects="projects"
                                       :item="editedTask"
                                       @task-updated="updateTask"></edit-task>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import Task from '../components/Todo/Task';
    import NewTask from '../components/Todo/NewTask';
    import EditTask from '../components/Todo/EditTask';

    export default {
        name: "Todo",

        components: {
            Task,
            NewTask,
            EditTask
        },

        data() {
            return {
                loading: false,
                items: [],
                projects: [],
                editedTask: null
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
            createTask(item) {
                this.items.push(item)
            },

            /**
             * Update task on the list
             * @param task
             */
            updateTask(task) {
                let index = this.items.findIndex(item => item.id === task.id);

                let project = this.projects.find(item => item.id === task.project_id);
                this.items[index].project = project;
                this.items[index].task = project.tasks.find(item => item.id === task.task_id);
                this.items[index].description = task.description;
            },

            /**
             * Delete item from the list
             * @param id
             */
            deleteTask(id) {
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
             * @param task
             */
            handleTaskUpdate(task) {
                this.editedTask = task;
                let self = this;
                $('#edit').on('hidden.bs.modal', function (e) {
                    self.editedTask = null;
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