<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>My account</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-lg-6">
                    <info></info>
                </div>
                <div class="col-lg-6">
                    <team></team>
                </div>
            </div>
        </section>
    </div>
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
