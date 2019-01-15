<script>
    import { Pie } from 'vue-chartjs'

    export default {
        name: 'PieChart',
        extends: Pie,
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