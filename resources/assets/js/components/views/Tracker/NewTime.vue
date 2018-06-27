<template>
    <div>
        <h1 class="text-center">New time log</h1><hr>

        <form @submit.prevent="create" @change="form.errors.clear($event.target.name)" @keydown="form.errors.clear($event.target.name)">
            <div class="form-group">
                <label for="name">Choose your project</label>
                <select class="form-control" name="project" v-model="form.project" :class="{ 'is-invalid': form.errors.has('project')}">
                    <option v-if="projects.length == 0" value="''" disabled selected="false">There are no projects that you could choose</option>
                    <option v-for="project in projects" :value="project.id">{{ project.name }}</option>
                </select>
                <form-error :text="form.errors.get('project')" :show="form.errors.has('project')"></form-error>
            </div>

            <div class="form-group">
                <label for="name">Pick your task</label>
                <select class="form-control" name="task" v-model="form.task" :class="{ 'is-invalid': form.errors.has('task')}">
                    <option v-if="tasks.length == 0" value="''" disabled selected="false">There are no tasks that you could pick</option>
                    <option v-for="task in tasks" :value="task.id">{{ task.name }} ({{ task.billable_text }})</option>
                </select>
                <form-error :text="form.errors.get('task')" :show="form.errors.has('task')"></form-error>
            </div>

            <div class="form-group">
                <label for="name">Description</label>
                <input id="name" type="text" class="form-control"
                       name="name" value="" placeholder="Description" v-model="form.description">
            </div>

            <div class="col-lg-12 text-center">
                <button class="btn btn-success">Create</button>
                <button type="button" data-dismiss="modal" aria-label="Close" id="closeDialog" class="btn btn-danger" >Cancel</button>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        props: ['projects'],

        data() {
            return {
                tasks: [],
                form: new Form({
                    user: this.$user.data.id,
                    project: '',
                    task: '',
                    description: '',
                })
            }
        },

        watch: {
            'form.project': function () {
                if (this.form.project) {
                    let project = this.projects.find(item => item.id == this.form.project);
                    this.tasks = project.tasks;
                }
            }
        },

        methods: {
            /**
             * Creates new tracking time row
             */
            create() {
                this.form.post('/api/times').then(response => {
                    let log = this.form.data();
                    log.id = response.data.id;
                    log.start = response.data.start;
                    log.length = response.data.length;
                    this.$parent.timeLogs.push(log);
                    this.form.reset();
                    this.form.user = this.$user.data.id;
                    $('#closeDialog').trigger('click');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>