<template>
    <div class="list-group-item pb-2"
         :class="{ 'bg-muted': item.finished}">
        <div class="row">
            <div class="col-lg-10">
                <div class="col-lg-12 p-0">
                    <small title="Project" class="badge badge-success">
                        <i class="fa fa-briefcase"></i> {{ item.project.name }}
                    </small>
                    <small title="Task type" v-if="item.task" class="badge badge-info">
                        <i class="fa fa-tasks"></i> {{ item.task.name }}
                    </small>
                    <priority-badge :priority="item.priority">
                        <i class="fa fa-signal"></i> Priority: {{ itemPriority }}
                    </priority-badge>
                </div>
                <div class="col-lg-12 mt-1 pl-0 pt-2 pb-0">
                    <h5>{{ item.description }}</h5>
                </div>
            </div>
            <div class="col-lg-2 todo-buttons text-right">
                <i @click="finish"
                   class="fa fa-check text-success btn"
                   title="Mark as finished"
                   v-if="!item.finished">
                </i>
                <i @click="finish"
                   class="fa fa-close text-danger btn"
                   title="Mark as unfinished"
                   v-if="item.finished">
                </i>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 item-action-buttons">
                <a v-if="!isActive && !item.finished" @click.prevent="start"
                   class="small text-success"
                   href="">
                    <i class="fa fa-play" title="Start"></i> start
                </a>
                <span v-if="isActive"
                   class="small text-secondary pr-1">
                    <i class="fa fa-clock-o tick"></i> working on it
                </span>
                <a @click.prevent="edit"
                   class="small text-primary"
                   data-target="#edit"
                   data-toggle="modal"
                   href=""
                   v-if="!item.finished">
                    <i class="fa fa-edit" title="Edit"></i> edit
                </a>
                <a @click.prevent="remove"
                   class="small text-danger"
                   href="">
                    <i class="fa fa-trash" title="Delete"></i> delete
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import PriorityBadge from './PriorityBadge';
    import {mapActions, mapGetters} from 'vuex';

    export default {
        name: "Task",

        components: {
            PriorityBadge
        },

        props: {
            item: {
                type: Object,
                required: true
            }
        },

        computed: {
            ...mapGetters('timelogs', {
                activeLogs: 'active'
            }),
            itemPriority() {
                let priorities = {
                    1: 'high',
                    2: 'medium',
                    3: 'low'
                };

                return priorities[this.item.priority];
            },
            isActive() {
                return !_.isUndefined(this.activeLogs.find(item => item.id === this.item.timelog_id));
            }
        },

        methods: {
            ...mapActions('timelogs', {
                startLog: 'create'
            }),

            finish() {
                axios.patch(`todos/${this.item.id}/status`, {
                    finished: !this.item.finished
                }).then(response => {
                    this.item.finished = !this.item.finished;
                }).catch(error => {
                    this.$awn.alert(error.message);
                })
            },

            start() {
                let log = {
                    member: this.item.member_id,
                    project: this.item.project_id,
                    task: this.item.task_id,
                    description: this.item.description,
                    duration: null,
                    created_at: moment().format('YYYY-MM-DD')
                };

                this.startLog(log).then(response => {
                    this.item.timelog_id = response.id;
                    axios.put(`todos/${this.item.id}`, this.item).then(response => {
                        this.$awn.success('New time log started');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    });
                }).catch(error => {
                    this.$awn.alert(error.response)
                });
            },

            edit() {
                this.$emit('updating-task', this.item)
            },

            remove() {
                this.$swal({
                    title: 'Are you sure?',
                    text: 'You won\'t be able to revert this!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#dd4b39',
                    focusCancel: true,
                    reverseButtons: true
                }).then(result => {
                    if (result.value) {
                        this.$emit('task-deleted', this.item.id);
                    }
                })
            }
        }
    }
</script>

<style scoped>
    .bg-muted {
        background-color: #e9e9e9;
    }

    .todo-buttons i {
        font-size: 24px;
    }

    .fa-check:hover {
        cursor: pointer;
        color: #218838 !important;
    }
</style>