<script>
    import { Bar } from 'vue-chartjs'

    export default {
        name: 'BarChart',
        extends: Bar,
        props: {
            data: {
                type: Object,
                required: true,
            },
            options: {
                type: Object,
                required: false,
                default: () => {}
            }
        },
        mounted () {
            this.renderChart(this.data, this.options)
        }
    }

</script>