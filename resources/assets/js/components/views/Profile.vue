<template>
    <section>
        <div class="row">
            <div class="col-md-10">
                <h1>My account</h1>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-6">
            <info></info>
        </div>
        <div class="col-lg-6">
            <team></team>   
        </div>
        </div>
    </section>   
</template>

<script>
    import Info from './Profile/Info.vue';
    import Team from './Profile/Team.vue';

    export default {
        components: {
            Info,
            Team
        },
    }
</script>
