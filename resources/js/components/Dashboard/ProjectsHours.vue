<template>
    <div class="card">
        <div class="card-header">
            <h3 class="d-inline">Hours per project worked this {{ this.period }}</h3>
            <div class="card-tools">
                <date-range :allow-custom="false" @change="applyRangeFilter"></date-range>
            </div>
        </div>
        <div class="card-body">
            <pie-chart v-if="chartData" :data="chartData" :options="chartOptions"></pie-chart>
            <p class="text-center mb-0 " v-else>No data</p>
        </div>
    </div>
</template>

<script>
    import PieChart from '../Charts/PieChart';
    import DateRange from '../Report/DateRange';
    import Color from '../../utilities/Color';

    export default {
        components: {
            PieChart, DateRange
        },
        data() {
            return {
                period: 'week',
                report: [],
                chartData: null,
                chartOptions: {
                    maintainAspectRatio: false,
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
                this.chartData = null;
                axios.get('api/reports/' + this.$user.get('id') + '/projects/' + this.period).then(response => {
                    let labelsNum = response.data.labels.length;
                    if (labelsNum === 0) {
                        return;
                    }

                    let colors = [];
                    for (let i = 0; i < labelsNum; i++) {
                        colors.push(Color.randomRgb());
                    }
                    this.chartData = {
                        labels: response.data.labels,
                        datasets: [
                            {
                                label: 'hours',
                                backgroundColor: colors,
                                data: response.data.hours
                            }
                        ]
                    };
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
        }
    }
</script>

<style scoped>

</style>