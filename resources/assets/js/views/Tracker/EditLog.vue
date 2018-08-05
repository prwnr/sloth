<template>
    <div>
        <h1 class="text-center">Edit time log</h1><hr>

        <form @submit.prevent="save" @change="form.errors.clear($event.target.name)" @keydown="form.errors.clear($event.target.name)">
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
                <div class="input-group">
                    <textarea id="name" type="text" class="form-control"
                              name="name" value="" placeholder="Description" v-model="form.description" :maxlength="200"></textarea>
                    <div class="input-group-append">
                        <span class="input-group-text" v-text="(200 - form.description.length)"></span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Date</label>
                <div class="input-group">
                    <div slot="afterDateInput" class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                    <date-picker :bootstrap-styling="true" format="yyyy-MM-dd" v-model="form.created_at"></date-picker>
                </div>
            </div>

            <div class="col-lg-12 p-0">
                <button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-danger" >Cancel</button>
                <button class="btn btn-success float-right">Save</button>
            </div>
        </form>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";
    import DatePicker from "vuejs-datepicker";

    export default {
        props: ['time', 'projects', 'day'],

        components: {
            DatePicker
        },

        data() {
            return {
                tasks: [],
                duration: null,
                timer: new Timer(),
                form: new Form({
                    user: this.$user.data.id,
                    project: this.time.project.id,
                    task: this.time.task ? this.time.task.id : null,
                    description: this.time.description ? this.time.description : '',
                    created_at: this.day
                })
            }
        },

        created() {
            this.fillTasks();
            EventHub.listen('new_current_day', day => {
                this.form.created_at = day;
            });
        },

        watch: {
            'form.project': function () {
                if (this.form.project) {
                    this.fillTasks();
                    this.form.task = null
                }
            }
        },

        methods: {
            /**
             * Fill tasks variable with array of tasks for current project
             */
            fillTasks() {
                let project = this.projects.find(item => item.id == this.form.project);
                this.tasks = project.tasks.filter(item => item.is_deleted == false);
            },

            /**
             * Update tracking time row
             */
            save() {
                this.form.created_at = moment(this.form.created_at).format('YYYY-MM-DD');
                this.form.put('/api/time/' + this.time.id).then(response => {
                    this.$emit('logUpdated', response.data);
                    $('#editRow').modal('hide');

                    if (this.day !== this.form.created_at) {
                        this.$emit('dateChanged');
                    }

                    this.$awn.success('Log successfully updated');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>