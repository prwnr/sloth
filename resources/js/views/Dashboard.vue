<template>
    <div class="content">
        <section class="content-header">
        </section>
        <section class="content">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-6 mb-3">
                        <total-hours></total-hours>
                    </div>
                    <div class="col-6 mb-3">
                        <projects-hours></projects-hours>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import TotalHours from '../components/Dashboard/TotalHours';
    import ProjectsHours from '../components/Dashboard/ProjectsHours';

    export default {
        components: {
            TotalHours, ProjectsHours
        }
    }
</script>
