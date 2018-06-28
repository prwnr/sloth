<template>
    <button type="button" class="btn btn-default btn-sm pull-right mb-3" @click="routerBack">
        <span class="fa fa-backward fa-fw"></span> Back
    </button>
</template>


<script>
export default {
    methods: {
        routerBack() {
            this.$router.go(-1)
        }
    }
}
</script>

<style scoped>

</style>
