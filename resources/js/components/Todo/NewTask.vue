<template>
    <div>
        <h1 class="text-center">New todo task</h1><hr>

        <form @submit.prevent="create" @change="form.errors.clear($event.target.name)" @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <label for="name">Write what you want to do</label>
                <div class="input-group">
                    <textarea id="description" type="text" class="form-control"
                              name="description" value="" placeholder="Description" v-model="form.description" :maxlength="500"></textarea>
                    <div class="input-group-append">
                        <span class="input-group-text" v-text="(500 - form.description.length)"></span>
                    </div>
                </div>
                <form-error :text="form.errors.get('description')" :show="form.errors.has('description')"></form-error>
            </div>

            <div class="form-group">
                <label for="project_id">Choose associated project</label>
                <select id="project_id" class="form-control" name="project_id" v-model="form.project_id" :class="{ 'is-invalid': form.errors.has('project_id')}">
                    <option v-if="projects.length == 0" value="''" disabled selected="false">There are no projects that you could choose</option>
                    <option v-for="project in projects" :value="project.id">{{ project.name }}</option>
                </select>
                <form-error :text="form.errors.get('project_id')" :show="form.errors.has('project_id')"></form-error>
            </div>

            <div class="form-group">
                <label for="task_id">Pick your task</label>
                <select id="task_id" class="form-control" name="task_id" v-model="form.task_id" :class="{ 'is-invalid': form.errors.has('task_id')}">
                    <option v-if="tasks.length == 0" value="''" disabled selected="false">There are no tasks that you could pick</option>
                    <option v-for="task in tasks" :value="task.id">{{ task.name }}</option>
                </select>
                <form-error :text="form.errors.get('task_id')" :show="form.errors.has('task_id')"></form-error>
            </div>

            <div class="form-group">
                <label class="">Priority of your task</label>
                <div class="form-group">
                    <div v-for="(priority, index) in priorities" class="form-check form-check-inline">
                        <label class="form-check-label" :for="'new_'+priority">
                            <input name="priority" :id="'new_'+priority" type="radio" class="form-check-input"
                                   v-model="form.priority"
                                   :value="index"
                                   :class="{ 'is-invalid': form.errors.has('priority')}">
                            <priority-badge :priority="index">{{ priority }}</priority-badge>
                        </label>
                    </div>
                </div>
                <form-error :text="form.errors.get('priority')" :show="form.errors.has('priority')"></form-error>
            </div>

            <div class="col-lg-12 p-0">
                <button type="button" data-dismiss="modal" aria-label="Close" id="closeDialog" class="btn btn-danger" >Cancel</button>
                <button class="btn btn-success float-right" type="submit">Create</button>
            </div>
        </form>
    </div>
</template>

<script>
    import PriorityBadge from './PriorityBadge';
    import {mapGetters} from "vuex";

    export default {
        name: "NewTask",

        components: {
            PriorityBadge
        },

        props: ['projects', 'priorities'],

        data() {
            return {
                tasks: [],
                form: new Form({
                    member_id: 0,
                    project_id: '',
                    task_id: '',
                    description: '',
                    finished: false,
                    priority: ''
                })
            }
        },

        created() {
            this.form.member_id = this.authUser.member.id
        },

        watch: {
            'form.project_id': function () {
                if (this.form.project_id) {
                    let project = this.projects.find(item => item.id === this.form.project_id);
                    this.tasks = project.tasks.filter(item => item.is_deleted == false);
                }

                this.form.task_id = null;
            }
        },

        computed: {
            ...mapGetters(['authUser'])
        },

        methods: {
            /**
             * Creates new to do task
             */
            create() {
                this.form.post('/api/todos').then(response => {
                    this.$emit('task-created', response.data);
                    this.$awn.success('New todo task created successfully');
                    this.form.reset();
                    this.form.member_id = this.authUser.member.id;
                    this.form.finished = false;
                    $('#new').modal('hide');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>
    .badge {
        font-size: 100% !important;
    }
</style>