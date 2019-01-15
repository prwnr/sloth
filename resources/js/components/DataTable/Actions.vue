<template>
    <div class="btn-group btn-group-lg">
        <div class="p-1">
            <router-link
                    :to="{ name: xprops.route + '.show', params: { id: row.id } }"
                    class="btn btn-info btn-sm btn-block">
                View
            </router-link>
        </div>
        <div class="p-1">
            <router-link
                    :to="{ name: xprops.route + '.edit', params: { id: row.id } }"
                    :class="{ disabled: !isEditable() }"
                    class="btn btn-success btn-sm btn-block">
                Edit
            </router-link>
        </div>
        <div class="p-1">
            <button @click="destroyData(row.id)" type="button"
                    :disabled="!isDeletable()"
                    class="btn btn-danger btn-sm btn-block">
                Delete
            </button>
        </div>
   </div>
</template>


<script>
export default {
    name: 'TableActions',
    props: {
        row: {
            type: Object,
            required: true
        },
        xprops: {
            type: Object,
            required: false,
            default: () => {}
        }
    },
    methods: {
        destroyData(id) {
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
                if (result.value && this.xprops.destroy) {
                    this.xprops.destroy(id);
                }
            })
        },

        /**
         * Check if row can be edited
         * @returns {*}
         */
        isEditable() {
            if (!this.row.hasOwnProperty('editable')) {
                return true;
            }

            return this.row.editable;
        },

        /**
         * Check if row can be deleted
         * @returns {*}
         */
        isDeletable() {
            if (!this.row.hasOwnProperty('deletable')) {
                return true;
            }

            return this.row.deletable;
        }
    },
}
</script>


<style scoped>

</style>
