<template>
    <div>
        <h1 class="text-center">New todo task</h1><hr>

        <form @submit.prevent="save" @change="form.errors.clear($event.target.name)" @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <label for="name">Write what you want to do</label>
                <div class="input-group">
                    <textarea id="name" type="text" class="form-control"
                              name="name" value="" placeholder="Description" v-model="form.description" :maxlength="500"></textarea>
                    <div class="input-group-append">
                        <span class="input-group-text" v-text="(500 - form.description.length)"></span>
                    </div>
                </div>
                <form-error :text="form.errors.get('description')" :show="form.errors.has('description')"></form-error>
            </div>

            <div class="form-group">
                <label for="name">Choose associated project</label>
                <select class="form-control" name="project" v-model="form.project_id" :class="{ 'is-invalid': form.errors.has('project_id')}">
                    <option v-if="projects.length == 0" value="''" disabled selected="false">There are no projects that you could choose</option>
                    <option v-for="project in projects" :value="project.id">{{ project.name }}</option>
                </select>
                <form-error :text="form.errors.get('project_id')" :show="form.errors.has('project_id')"></form-error>
            </div>

            <div class="form-group">
                <label for="name">Pick your task</label>
                <select class="form-control" name="task" v-model="form.task_id" :class="{ 'is-invalid': form.errors.has('task_id')}">
                    <option v-if="tasks.length == 0" value="''" disabled selected="false">There are no tasks that you could pick</option>
                    <option v-for="task in tasks" :value="task.id">{{ task.name }}</option>
                </select>
                <form-error :text="form.errors.get('task_id')" :show="form.errors.has('task_id')"></form-error>
            </div>
            <div class="col-lg-12 p-0">
                <button type="button" data-dismiss="modal" aria-label="Close" id="closeDialog" class="btn btn-danger" >Cancel</button>
                <button class="btn btn-success float-right" type="submit">Save</button>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        name: "EditTask",

        props: ['item', 'projects'],

        data() {
            return {
                tasks: [],
                form: new Form({
                    member_id: this.$user.member.id,
                    project_id: '',
                    task_id: this.item.task ? this.item.task.id : 0,
                    description: this.item.description
                })
            }
        },

        created() {
            this.form.project_id = this.item.project.id;
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

        methods: {
            /**
             * Save task update
             */
            save() {
                this.form.put(`/api/todos/${this.item.id}`).then(response => {
                    this.$emit('task-updated', response.data);
                    this.$awn.success('Todo task successfully updated');
                    this.form.reset();
                    $('#edit').modal('hide');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>