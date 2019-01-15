<template>
    <div class="card-header">
        <h5 class="card-title"><slot></slot></h5>
        <div class="card-tools">
            <button v-if="minimizable" type="button" class="btn btn-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse">
                <i class="fa fa-minus"></i></button>
            <button v-if="closable" type="button" class="btn btn-tool" data-widget="remove" data-toggle="tooltip" title="Remove">
                <i class="fa fa-times"></i></button>
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