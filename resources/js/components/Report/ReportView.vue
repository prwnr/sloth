<template>
    <div class="card mb-3">
        <card-header>Report</card-header>
        <table class="table border-top-0">
            <tr class="border-top-0">
                <td><strong>Total hours</strong></td>
                <td>{{ data.total_hours }}</td>
            </tr>
            <tr class="border-top-0">
                <td><strong>Total billable hours</strong></td>
                <td>{{ data.total_billable_hours }}</td>
            </tr>
            <tr>
                <td><strong>Total sales</strong></td>
                <td><span :class="budgetLevel">{{ data.total_sale }}</span> <span class="small" v-if="budget">({{ budgetPercentage() }}%)</span></td>
            </tr>
        </table>
    </div>
</template>

<script>
    export default {
        props: {
            data: {
                type: Object,
                default: () => {
                    return {
                        total_hours: 0,
                        total_sale: 0
                    }
                }
            },

            budget: {
                type: Number,
                default: 0
            }
        },

        computed: {
            budgetLevel: function () {
                if (!this.budget) {
                    return '';
                }

                let percentage = this.budgetPercentage();
                if (percentage < 70) {
                    return 'text-success';
                }

                if (percentage < 85) {
                    return 'text-warning';
                }

                if (percentage > 86) {
                    return 'text-danger';
                }
            }
        },

        methods: {
            budgetPercentage: function () {
                return Math.round((this.data.total_sale / this.budget) * 100);
            }
        }
    }
</script>

<style scoped>

</style>