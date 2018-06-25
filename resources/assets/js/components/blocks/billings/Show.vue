<template>
    <div>
        <strong>{{ billing.rate }} {{ billing.currency.name }}</strong>
        ({{ billing.currency.symbol }}) - {{ billing.type }} rate
    </div>
</template>

<script>
    export default {
        name: "Billing_Show",
        props: ['billing']
    }
</script>

<style scoped>

</style>