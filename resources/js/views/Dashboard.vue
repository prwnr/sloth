<template>
    <div class="content">
        <section class="content-header">
        </section>
        <section class="content">
            <div class="col-lg-12">
                <div v-if="$user.can('view_reports')" class="row">
                    <div class="col-12">
                        <h2>Reports charts</h2>
                    </div>
                    <div class="col-12">
                        <sales></sales>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h2>Personal charts</h2>
                    </div>
                    <div class="col-6 mb-5">
                        <total-hours></total-hours>
                    </div>
                    <div class="col-6 mb-5">
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
    import Sales from '../components/Dashboard/Sales';

    export default {
        components: {
            TotalHours, ProjectsHours, Sales
        }
    }
</script>
