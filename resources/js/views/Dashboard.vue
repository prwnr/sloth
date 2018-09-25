<template>
    <div class="content">
        <section class="content-header">
        </section>
        <section class="content">
            <div class="row ">
                <div class="col-lg-12">
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <card-header :minimizable="false">Total hours worked per month</card-header>
                            <div class="card-body">
                                <bar-chart v-if="chartData" :data="chartData" :options="{maintainAspectRatio: false}"></bar-chart>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import BarChart from '../components/Charts/BarChart';

    export default {
        components: {
            BarChart
        },
        data() {
            return {
                loaded: false,
                report: [],
                chartData: null
            }
        },

        created() {
            axios.get('api/reports/' + this.$user.get('id') + '/hours/year').then(response => {
                this.chartData = {
                    labels: response.data.labels,
                    datasets: [
                        {
                            label: 'hours',
                            backgroundColor: '#f87979',
                            data: response.data.hours
                        }
                    ]
                };
                this.loaded = true;
            }).catch(error => {
                this.$awn.alert(error.message);
            });
        }
    }
</script>
