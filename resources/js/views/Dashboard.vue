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
                                <bar-chart :data="chartData"></bar-chart>
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
                chartData: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'Septemer', 'October', 'November', 'December'],
                    datasets: [
                        {
                            label: 'total hours worked',
                            backgroundColor: '#f87979',
                            data: [0, 0, 0, 100, 20, 100, 170, 30, 0, 0, 0, 12]
                        }
                    ]
                }

            }
        },

        created() {
            axios.get('api/');
        }
    }
</script>
