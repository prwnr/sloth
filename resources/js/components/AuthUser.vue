<template>
    <div></div>
</template>

<script>
    import User from '../models/User.js';

    export default {
        props: ['user'],

        created() {
            Vue.prototype.$user = new User(this.user);
        }
    }
</script>

<style scoped>

</style>