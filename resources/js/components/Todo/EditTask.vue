<template>
    <div>
        <h1 class="text-center">New todo task</h1><hr>

        <form @submit.prevent="save" @change="form.errors.clear($event.target.name)" @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <label for="description">Write what you want to do</label>
                <div class="input-group">
                    <textarea :maxlength="500"
                              class="form-control"
                              id="description"
                              name="description"
                              placeholder="Description"
                              type="text"
                              v-model="form.description">
                    </textarea>
                    <div class="input-group-append">
                        <span class="input-group-text"
                              v-text="(200 - form.description.length)">
                        </span>
                    </div>
                </div>
                <form-error
                        :text="form.errors.get('description')"
                        :show="form.errors.has('description')">
                </form-error>
            </div>

            <div class="form-group">
                <label for="project">Choose associated project</label>
                <select :class="{ 'is-invalid': form.errors.has('project_id')}"
                        class="form-control"
                        name="project"
                        v-model="form.project_id">
                    <option
                            disabled
                            selected="false"
                            v-if="projects.length == 0"
                            value="''">
                        There are no projects that you could choose
                    </option>
                    <option
                            v-for="project in projects"
                            :value="project.id">
                        {{ project.name }}
                    </option>
                </select>
                <form-error
                        :text="form.errors.get('project_id')"
                        :show="form.errors.has('project_id')">
                </form-error>
            </div>

            <div class="form-group">
                <label for="task">Pick your task</label>
                <select :class="{ 'is-invalid': form.errors.has('task_id')}"
                        class="form-control"
                        name="task"
                        v-model="form.task_id">
                    <option disabled
                            selected="false"
                            v-if="tasks.length == 0"
                            value="''">
                        There are no tasks that you could pick
                    </option>
                    <option
                            v-for="task in tasks"
                            :value="task.id">
                        {{ task.name }}
                    </option>
                </select>
                <form-error
                        :text="form.errors.get('task_id')"
                        :show="form.errors.has('task_id')">
                </form-error>
            </div>

            <div class="form-group">
                <label class="">Priority of your task</label>
                <div class="form-group">
                    <div class="form-check form-check-inline"
                         v-for="(priority, index) in priorities">
                        <label class="form-check-label" :for="'edit_'+priority">
                            <input :class="{ 'is-invalid': form.errors.has('priority')}"
                                   :id="'edit_'+priority"
                                   :value="index"
                                   class="form-check-input"
                                   type="radio"
                                   v-model="form.priority">
                            <priority-badge :priority="index">
                                {{ priority }}
                            </priority-badge>
                        </label>
                    </div>
                </div>
                <form-error
                        :text="form.errors.get('priority')"
                        :show="form.errors.has('priority')">
                </form-error>
            </div>

            <div class="col-lg-12 p-0">
                <button type="button" data-dismiss="modal" aria-label="Close" id="closeDialog" class="btn btn-danger" >Cancel</button>
                <button class="btn btn-success float-right" type="submit">Save</button>
            </div>
        </form>
    </div>
</template>

<script>
    import PriorityBadge from './PriorityBadge';
    import {mapGetters} from 'vuex';

    export default {
        name: "EditTask",

        components: {
            PriorityBadge
        },

        props: ['item', 'projects', 'priorities'],
        props: {
            item: {
                type: Object,
                required: true
            },
            projects: {
                type: Array,
                required: true
            },
            priorities: {
                type: Object,
                required: true
            }
        },

        data() {
            return {
                tasks: [],
                form: new Form({
                    member_id: 0,
                    project_id: this.item.project_id,
                    task_id: this.item.task_id ? this.item.task_id : 0,
                    description: this.item.description,
                    priority: this.item.priority
                })
            }
        },

        created() {
            this.fillTasks();
            this.form.member_id = this.authUser.member.id
        },

        watch: {
            'form.project_id': function () {
                if (this.form.project_id) {
                    this.fillTasks();
                }

                this.form.task_id = null;
            }
        },

        computed: {
            ...mapGetters(['authUser'])
        },

        methods: {
            /**
             * Save task update
             */
            save() {
                this.form.put(`todos/${this.item.id}`).then(response => {
                    this.$emit('task-updated', response.data);
                    this.$awn.success('Todo task successfully updated');
                    this.form.reset();
                    $('#edit').modal('hide');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Fill tasks variable with array of tasks for current project
             */
            fillTasks() {
                let project = this.projects.find(item => item.id === this.form.project_id);
                this.tasks = project.tasks.filter(item => item.is_deleted == false);
            },
        }
    }
</script>

<style scoped>
    .badge {
        font-size: 100% !important;
    }
</style>