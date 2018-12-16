<template>
    <div class="list-group-item pb-2">
        <div class="row">
            <div class="col-lg-10">
                <div class="col-lg-12 p-0">
                    Project: {{ item.project.name }} <span v-if="item.task">({{ item.task.name }})</span>
                </div>
                <div class="col-lg-12 mt-1 pl-1 pt-2 pb-0">
                    <h5>{{ item.description }}</h5>
                </div>
            </div>
            <div class="col-lg-2 todo-buttons text-right">
                <i class="fa fa-check text-success btn" @click="finish" title="Mark as finished"></i>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 item-action-buttons">
                <a href="" @click.prevent="edit" class="small text-primary" data-toggle="modal" data-target="#edit">
                    <i class="fa fa-edit" title="Edit"></i> edit
                </a>
                <a href="" @click.prevent="remove" class="small text-danger"><i class="fa fa-trash" title="Delete"></i> delete</a>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Task",

        props: ['item'],

        methods: {
            finish() {

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
    .todo-buttons i {
        font-size: 24px;
    }

    .fa-check:hover {
        cursor: pointer;
        color: #218838 !important;
    }
</style>