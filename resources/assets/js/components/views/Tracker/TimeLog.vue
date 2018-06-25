<template>
    <div class="row form-group">
        <div class="col-lg-7">
            {{ time.project.name }} ({{ time.project.code }})
        </div>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="time">
        </div>
        <div class="col-lg-3">
            <button class="btn btn-success">Start time</button>
            <button class="btn btn-default">Edit</button>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['time'],
    }
</script>

<style scoped>

</style>