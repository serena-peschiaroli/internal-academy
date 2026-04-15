<template>
    <div />
</template>

<script lang="ts">
import { h } from 'vue';

// Eye Icon
export const EyeIcon = {
    name: 'EyeIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('path', { d: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z' }),
            h('circle', { cx: '12', cy: '12', r: '3' })
        ]);
    }
};

// Filter Icon
export const FilterIcon = {
    name: 'FilterIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('polygon', { points: '22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3' })
        ]);
    }
};

// Filter X Icon (Clear filters)
export const FilterXIcon = {
    name: 'FilterXIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('polygon', { points: '22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3' }),
            h('line', { x1: '18', y1: '6', x2: '14', y2: '10' }),
            h('line', { x1: '14', y1: '6', x2: '18', y2: '10' })
        ]);
    }
};

// X Circle Icon (Remove single filter)
export const XCircleIcon = {
    name: 'XCircleIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('circle', { cx: '12', cy: '12', r: '10' }),
            h('line', { x1: '15', y1: '9', x2: '9', y2: '15' }),
            h('line', { x1: '9', y1: '9', x2: '15', y2: '15' })
        ]);
    }
};

// Arrow Up Icon (Sort ascending)
export const ArrowUpIcon = {
    name: 'ArrowUpIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('line', { x1: '12', y1: '19', x2: '12', y2: '5' }),
            h('polyline', { points: '5 12 12 5 19 12' })
        ]);
    }
};

// Arrow Down Icon (Sort descending)
export const ArrowDownIcon = {
    name: 'ArrowDownIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('line', { x1: '12', y1: '5', x2: '12', y2: '19' }),
            h('polyline', { points: '19 12 12 19 5 12' })
        ]);
    }
};

// Arrows Up Down Icon (Sortable)
export const ArrowsUpDownIcon = {
    name: 'ArrowsUpDownIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('path', { d: 'M7 15l5 5 5-5' }),
            h('path', { d: 'M7 9l5-5 5 5' })
        ]);
    }
};

// Check Icon (Selected option)
export const CheckIcon = {
    name: 'CheckIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('polyline', { points: '20 6 9 17 4 12' })
        ]);
    }
};

// Chevron Down Icon (Dropdown indicator)
export const ChevronDownIcon = {
    name: 'ChevronDownIcon',
    render() {
        return h('svg', {
            xmlns: 'http://www.w3.org/2000/svg',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': '2',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            class: 'w-4 h-4'
        }, [
            h('polyline', { points: '6 9 12 15 18 9' })
        ]);
    }
};

export default {
    EyeIcon,
    FilterIcon,
    FilterXIcon,
    XCircleIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ArrowsUpDownIcon,
    CheckIcon,
    ChevronDownIcon
};
</script>
