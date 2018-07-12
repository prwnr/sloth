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
                <div class="input-group">
                    <textarea id="name" type="text" class="form-control"
                              name="name" value="" placeholder="Description" v-model="form.description" :maxlength="200"></textarea>
                    <div class="input-group-append">
                        <span class="input-group-text" v-text="(200 - form.description.length)"></span>
                    </div>
                </div>
            </div>

            <div class="form-group col-5 p-0">
                <label for="name">Time <span class="small">(optional)</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                    </div>
                    <input type="text" class="form-control text-right" name="time"
                           v-model="duration"
                           v-mask="'##:##'"
                           placeholder="00:00"
                           @keyup="correctTime"/>
                </div>
            </div>

            <div class="col-lg-12 p-0">
                <button type="button" data-dismiss="modal" aria-label="Close" id="closeDialog" class="btn btn-danger" >Cancel</button>
                <button class="btn btn-success float-right">{{ buttonText }}</button>
            </div>
        </form>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";

    export default {
        props: ['projects'],

        data() {
            return {
                tasks: [],
                duration: null,
                timer: new Timer(),
                form: new Form({
                    user: this.$user.data.id,
                    project: '',
                    task: '',
                    description: '',
                    duration: null
                })
            }
        },

        computed: {
            buttonText: function () {
                return this.duration ? 'Create' : 'Start';
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
                if (this.duration) {
                    this.form.duration = this.timer.secondsToMinutes(this.timer.revert(this.duration));
                }

                this.form.post('/api/time').then(response => {
                    if (response.data.start) {
                        response.data.start = response.data.start.date;
                    } else {
                        response.data.start = null;
                    }

                    this.$parent.timeLogs.push(response.data);
                    this.form.reset();
                    this.form.user = this.$user.data.id;
                    this.duration = null;
                    $('#closeDialog').trigger('click');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Makes sure that MM in HH:MM won't go over 60 minutes
             */
            correctTime() {
                let time = this.duration.split(':');
                if (time.length != 2) {
                    return;
                }

                if (time[1] > 60) {
                    this.duration = time[0] + ':' + 60;
                }
            }
        }
    }
</script>