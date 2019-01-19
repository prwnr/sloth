<template>
    <div class="card-header">
        <h5 class="card-title"><slot></slot></h5>
        <div class="card-tools">
            <button class="btn btn-tool"
                    data-toggle="tooltip"
                    data-widget="collapse"
                    title="Collapse"
                    type="button"
                    v-if="minimizable">
                <i class="fa fa-minus"></i>
            </button>
            <button class="btn btn-tool"
                    data-toggle="tooltip"
                    data-widget="remove"
                    title="Remove"
                    type="button"
                    v-if="closable">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CardHeader",
        props: {
            minimizable: {
                type: Boolean,
                default: true,
            },
            closable: {
                type: Boolean,
                default: false,
            }
        }
    }
</script>

<style scoped>

</style>