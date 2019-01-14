<template>
    <div class="card">
        <div class="card-header">
            <h3 class="d-inline">Projects sales this {{ this.period }}</h3>
            <div class="card-tools">
                <date-range :allow-custom="false" @change="applyRangeFilter"></date-range>
            </div>
        </div>
        <div class="card-body">
            <line-chart v-if="chartData" :data="chartData" :options="chartOptions"></line-chart>
            <p class="text-center mb-0 " v-else>No data</p>
        </div>
    </div>
</template>

<script>
    import LineChart from '../Charts/LineChart';
    import DateRange from '../Report/DateRange';
    import Color from '../../utilities/Color';

    export default {
        components: {
            LineChart, DateRange
        },
        data() {
            return {
                period: 'week',
                chartData: null,
                chartOptions: {
                    maintainAspectRatio: false,
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                        }],
                        yAxes: [{
                            display: true,
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
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
                let color = new Color();
                this.chartData = null;

                axios.get('reports/sales/' + this.period).then(response => {
                    let datasets = [];
                    for (let index in response.data.sales) {
                        let project = response.data.sales[index];
                        let setColor = color.random();
                        datasets.push({
                            label: project['label'],
                            borderColor: setColor,
                            backgroundColor: setColor,
                            data: Object.values(project['data']),
                            fill: false,
                        });
                    }

                    this.chartData = {
                        labels: response.data.labels,
                        datasets: datasets
                    };
                }).catch(error => {
                    this.$awn.alert(error.message);
                }).finally(() => {
                    color.clearGenerated();
                });
            },
        }
    }
</script>

<style scoped>

</style>