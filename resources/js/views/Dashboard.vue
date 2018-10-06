<template>
    <div class="content">
        <section class="content-header">
        </section>
        <section class="content">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="col-lg-6 mb-3">
                        <total-hours></total-hours>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import TotalHours from '../components/Dashboard/TotalHours';

    export default {
        components: {
            TotalHours
        }
    }
</script>
