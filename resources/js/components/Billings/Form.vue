<template>
    <div class="row">
        <div class="col-4">
            <input id="billing_rate" type="number" step=".01" class="form-control" v-model="$parent.form.billing_rate"
                name="billing_rate" value="" placeholder="Rate" required>
            <form-error :text="$parent.form.errors.get('billing_rate')" :show="$parent.form.errors.has('billing_rate')"></form-error>
        </div>
        <div class="col-4">
            <select class="form-control" name="billing_currency" v-model="$parent.form.billing_currency" required>
                <option :value="0" selected disabled>Currency</option>
                <option v-for="currency in currencies" :value="currency.id" :key="currency.id">{{ currency.symbol }} {{ currency.name }}</option>
            </select>
            <form-error :text="$parent.form.errors.get('billing_currency')" :show="$parent.form.errors.has('billing_currency')"></form-error>
        </div>
        <div class="col-4">
             <select class="form-control" name="billing_currency" v-model="$parent.form.billing_type" required>
                <option :value="''" selected disabled>Billing type</option>
                <option v-for="(billingType, index) in billingTypes" :value="index" :key="index">{{ billingType }}</option>
            </select>
            <form-error :text="$parent.form.errors.get('billing_type')" :show="$parent.form.errors.has('billing_type')"></form-error>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['currencies', 'billingTypes'],
    }
</script>
