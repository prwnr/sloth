<template>
    <div class="card">
        <div class="card-header">
            <h3 class="d-inline">Total hours worked this {{ this.period }}</h3>
            <div class="card-tools">
                <date-range :allow-custom="false" @change="applyRangeFilter"></date-range>
            </div>
        </div>
        <div class="card-body">
            <bar-chart v-if="chartData" :data="chartData" :options="chartOptions"></bar-chart>
        </div>
    </div>
</template>

<script>
    import BarChart from '../Charts/BarChart';
    import DateRange from '../Report/DateRange';
    import Color from '../../utilities/Color';

    export default {
        components: {
            BarChart, DateRange
        },
        data() {
            return {
                period: 'week',
                report: [],
                chartData: null,
                chartOptions: {
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                },
                background: Color.randomRgb()
            }
        },

        created() {
            this.fetchData();
        },

        methods: {
            /**
             * @param range
             */
            applyRangeFilter(range) {
                this.period = range;
                this.fetchData();
            },

            /**
             * Fetch chart data
             */
            fetchData() {
                this.chartData = null;
                axios.get('api/reports/' + this.$user.get('id') + '/hours/' + this.period).then(response => {
                    this.chartData = {
                        labels: response.data.labels,
                        datasets: [
                            {
                                label: 'hours',
                                backgroundColor: this.background,
                                data: response.data.hours
                            }
                        ]
                    };
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>