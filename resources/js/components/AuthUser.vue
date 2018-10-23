<template>
    <div></div>
</template>

<script>
    import User from '../models/User.js';

    export default {
        props: ['user'],

        created() {
            Vue.prototype.$user = new User(this.user);
            EventHub.listen('team_change', (user) => {
                Vue.prototype.$user = new User(user);
                EventHub.fire('user_change');
            });
        }
    }
</script>

<style scoped>

</style>