<template>
    <div class="-nested-dsp-row-comp">
        <button class="btn btn-xs btn-link -nested-dsp-row-close-btn"
                @click="nested.$toggle(false)">
            <i class="fa fa-times fa-lg"></i>
        </button>
        <dl class="dl-horizontal">
            <template v-for="(val, key) in row">

            </template>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['row', 'nested']
    }
</script>

<style>
    .-nested-dsp-row-comp {
        position: relative;
        padding: 10px;
    }

    .-nested-dsp-row-close-btn {
        position: absolute;
        top: 5px;
        right: 5px;
    }
</style>