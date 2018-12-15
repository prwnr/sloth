<template>
    <div class="list-group-item pb-2">
        <div class="row">
            <div class="col-lg-10">
                <div class="col-lg-12 p-0">
                    Project: {{ item.project.name }} ({{ item.task.type }})
                </div>
                <div class="col-lg-12 mt-1 pl-1 pt-2 pb-0">
                    <h5>{{ item.description }}</h5>
                </div>
            </div>
            <div class="col-lg-2 todo-buttons text-right">
                <i class="fa fa-trash text-danger btn" @click="remove" title="Remove"></i>
                <i class="fa fa-check text-success btn" @click="finish" title="Mark as finished"></i>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Todo",

        props: ['item'],

        methods: {
            finish() {

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
                        this.$emit('item-deleted', this.item.id);
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

    .fa-trash:hover {
        cursor: pointer;
        color: #c82333 !important;
    }
</style>