<template>
    <div class="-nested-dsp-row-comp">
        <button class="btn btn-xs btn-link -nested-dsp-row-close-btn"
                @click="nested.$toggle(false)">
            <i class="fa fa-times fa-lg"></i>
        </button>
        <h5>Log details</h5>
        <dl class="dl-horizontal">
            <span class="pr-3"><strong>Member earned</strong>: {{ details.member_salary.amount }} {{ details.member_salary.currency }}</span>
            <span class="pr-3"><strong>Earned on the project</strong>: {{ details.project_salary.amount }} {{ details.project_salary.currency }}</span>
            <span class="pr-3"><strong>Client salary</strong>: {{ details.client_salary.amount }} {{ details.client_salary.currency }}</span>
            <br>
            <strong>Task description</strong>: <span v-if="details.description">{{ details.description }}</span> <span v-else class="small">(none)</span>
        </dl>
    </div>
</template>

<script>
    export default {
        props: ['row', 'nested'],

        data () {
            return {
                details: this.row.details ? this.row.details : {}
            }
        }
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