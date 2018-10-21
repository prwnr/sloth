<template>
    <div>
        <h1 class="text-center">New time log</h1><hr>

        <form @submit.prevent="create" @change="form.errors.clear($event.target.name)" @keydown="form.errors.clear($event.target.name)">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="name">Choose your project</label>
                        <select class="form-control" name="project" v-model="form.project" :class="{ 'is-invalid': form.errors.has('project')}">
                            <option v-if="projects.length == 0" value="''" disabled selected="false">There are no projects that you could choose</option>
                            <option v-for="project in projects" :value="project.id">{{ project.name }}</option>
                        </select>
                        <form-error :text="form.errors.get('project')" :show="form.errors.has('project')"></form-error>
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label for="name">Select day</label>
                        <date-picker :bootstrap-styling="true" format="yyyy-MM-dd" v-model="form.created_at" :monday-first="true"></date-picker>
                    </div>
                </div>
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
                <label for="name">Write description <span class="small">(optional)</span></label>
                <div class="input-group">
                    <textarea id="name" type="text" class="form-control"
                              name="name" value="" placeholder="Description" v-model="form.description" :maxlength="200"></textarea>
                    <div class="input-group-append">
                        <span class="input-group-text" v-text="(200 - form.description.length)"></span>
                    </div>
                </div>
            </div>

            <div class="form-group col-5 p-0">
                <label for="name">Time <span v-if="!disableStartButton" class="small">(optional)</span></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                    </div>
                    <time-input v-model="duration"></time-input>
                </div>
            </div>

            <div class="col-lg-12 p-0">
                <button type="button" data-dismiss="modal" aria-label="Close" id="closeDialog" class="btn btn-danger" >Cancel</button>
                <button :disabled="disableStartButton && !duration" class="btn btn-success float-right">{{ buttonText }}</button>
            </div>
        </form>
    </div>
</template>

<script>
    import Timer from "../../utilities/Timer";
    import DatePicker from "vuejs-datepicker";
    import TimeInput from "./TimeInput";

    export default {
        props: ['projects', 'day'],

        components: {
            DatePicker, TimeInput
        },

        data() {
            return {
                tasks: [],
                duration: null,
                timer: new Timer(),
                form: new Form({
                    member: this.$user.member.id,
                    project: '',
                    task: '',
                    description: '',
                    duration: null,
                    created_at: this.day
                })
            }
        },

        created() {
            EventHub.listen('new_current_day', day => {
                this.form.created_at = day;
            })
        },

        computed: {
            buttonText: function () {
                return this.duration ? 'Create' : 'Start';
            },

            disableStartButton: function () {
                let selectedDate = moment(this.form.created_at);
                if (this.$parent.currentDay != selectedDate.format('YYYY-MM-DD')) {
                    return true;
                }

                return false;
            },
        },

        watch: {
            'form.project': function () {
                if (this.form.project) {
                    let project = this.projects.find(item => item.id === this.form.project);
                    this.tasks = project.tasks.filter(item => item.is_deleted == false);
                    this.form.task = null;
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

                this.form.created_at = moment(this.form.created_at).format('YYYY-MM-DD');
                this.form.post('/api/time').then(response => {
                    if (response.data.start) {
                        response.data.start = response.data.start.date;
                    } else {
                        response.data.start = null;
                    }

                    let created_at = this.form.created_at;
                    if (this.day === created_at) {
                        this.$emit('logAdded', response.data);
                    }

                    EventHub.fire('log_created', response.data);
                    this.form.reset();
                    this.form.created_at = created_at;
                    this.form.member = this.$user.member.id;
                    this.duration = null;
                    this.$awn.success('New time log created');
                    $('#newLog').modal('hide');
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>