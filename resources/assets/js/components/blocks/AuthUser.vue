<template>
    <div></div>
</template>

<script>
    import User from '../../classes/user.js';

    export default {
        props: ['user'],

        created() {
            Vue.prototype.$user = new User(this.user);
        }
    }
</script>

<style scoped>

</style>