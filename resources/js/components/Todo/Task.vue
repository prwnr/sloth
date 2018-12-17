<template>
    <div class="list-group-item pb-2" :class="{ 'bg-muted': item.finished}">
        <div class="row">
            <div class="col-lg-10">
                <div class="col-lg-12 p-0">
                    <small title="Project" class="badge badge-success"><i class="fa fa-briefcase"></i> {{ item.project.name }}</small>
                    <small title="Task type" v-if="item.task" class="badge badge-info"><i class="fa fa-tasks"></i> {{ item.task.name }}</small>
                    <priority-badge :priority="item.priority"><i class="fa fa-signal"></i> Priority: {{ itemPriority }} </priority-badge>
                </div>
                <div class="col-lg-12 mt-1 pl-0 pt-2 pb-0">
                    <h5>{{ item.description }}</h5>
                </div>
            </div>
            <div class="col-lg-2 todo-buttons text-right">
                <i v-if="!item.finished" class="fa fa-check text-success btn" @click="finish" title="Mark as finished"></i>
                <i v-if="item.finished" class="fa fa-close text-danger btn" @click="finish" title="Mark as unfinished"></i>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 item-action-buttons">
                <a v-if="!item.finished" href="" @click.prevent="edit" class="small text-primary" data-toggle="modal" data-target="#edit">
                    <i class="fa fa-edit" title="Edit"></i> edit
                </a>
                <a href="" @click.prevent="remove" class="small text-danger"><i class="fa fa-trash" title="Delete"></i> delete</a>
            </div>
        </div>
    </div>
</template>

<script>
    import PriorityBadge from './PriorityBadge';

    export default {
        name: "Task",

        components: {
            PriorityBadge
        },

        props: ['item'],

        computed: {
            itemPriority() {
                let priorities = {
                    1: 'high',
                    2: 'medium',
                    3: 'low'
                };

                return priorities[this.item.priority];
            }
        },

        methods: {
            finish() {
                axios.patch(`/api/todos/${this.item.id}/status`, {
                    finished: !this.item.finished
                }).then(response => {
                    this.item.finished = !this.item.finished;
                }).catch(error => {
                    this.$awn.alert(error.message);
                })
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