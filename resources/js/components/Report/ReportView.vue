<template>
    <div class="card mb-3">
        <card-header>Report</card-header>
        <div class="card-body p-0">
            <table class="table table-card">
                <tr v-for="(item, index) in items" :key="index">
                    <td><strong>{{ item.title }}</strong></td>
                    <td>{{ item.value }}</td>
                </tr>
                <tr v-if="showBudget">
                    <td><strong>Budget used</strong></td>
                    <td><span :class="budgetLevel">{{ budgetPercentage() }}%</span></td>
                </tr>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ReportView',
        props: {
            items: {
                type: Array,
                required: false,
                default: () => []
            },

            budget: {
                type: Object,
                default: () => {
                    return {
                        sale: 0,
                        total: 0
                    }
                }
            }
        },

        data() {
            return {
                showBudget: false
            }
        },

        created() {
            if (this.budget.sale && this.budget.total) {
                this.showBudget = true;
            }
        },

        computed: {
            budgetLevel: function () {
                if (!this.showBudget) {
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
                return Math.round((this.budget.sale / this.budget.total) * 100);
            }
        }
    }
</script>

<style scoped>

</style>