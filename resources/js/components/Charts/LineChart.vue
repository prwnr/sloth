<script>
    import { Line } from 'vue-chartjs'

    export default {
        name: 'LineChart',
        extends: Line,
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