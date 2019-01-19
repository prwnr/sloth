<template>
    <div>
        <strong>{{ billing.rate }} {{ billing.currency.name }}</strong>
        ({{ billing.currency.symbol }}) - {{ billing.type }} rate
    </div>
</template>

<script>
    export default {
        name: "BillingShow",
        props: {
            billing: {
                type: Object,
                required: true
            }
        }
    }
</script>

<style scoped>

</style>