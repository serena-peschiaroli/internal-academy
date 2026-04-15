<script setup lang="ts">
import { Eye, EyeOff, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import AtomButton from '@/components/Atoms/Button/index.vue';
import AtomInput from '@/components/Atoms/Input/index.vue';
import AtomSpinner from '@/components/Atoms/Spinner/index.vue';
import { cn } from '@/lib/utils';

type Row = Record<string, unknown> & { id?: string | number };

type TableButton = {
    key: string;
    title: string;
    variant?: 'primary' | 'outlined' | 'outline' | 'text' | 'delete' | 'danger' | 'destructive' | 'secondary' | 'ghost' | 'light' | 'warning' | 'transparent' | 'link' | 'default';
    icon?: 'adjustments' | 'plus' | 'arrowDown' | 'upload';
    onClick: () => void;
};

type TableFilterOption = {
    label: string;
    value: string;
};

type TableFilter = {
    key: string;
    label: string;
    type?: 'text' | 'select';
    placeholder?: string;
    columnKey?: string;
    options?: TableFilterOption[];
};

type TableColumn = {
    key: string;
    label: string;
    sortable?: boolean;
    visible?: boolean;
    hideable?: boolean;
    slot?: string;
    format?: (row: Row) => unknown;
};

const props = withDefaults(
    defineProps<{
        rows: Row[];
        columns: TableColumn[];
        loading?: boolean;
        buttons?: TableButton[];
        readOnly?: boolean;
        tableKey?: string | null;
        filtersConfig?: TableFilter[];
    }>(),
    {
        loading: false,
        buttons: () => [],
        readOnly: false,
        tableKey: null,
        filtersConfig: () => [],
    },
);

const emit = defineEmits<{
    (e: 'update:sort', payload: { key: string | null; direction: 'asc' | 'desc' | null }): void;
    (e: 'update:visible-columns', payload: string[]): void;
    (e: 'update:filters', payload: Record<string, unknown>): void;
}>();

const sortKey = ref<string | null>(null);
const sortDirection = ref<'asc' | 'desc' | null>(null);
const openColumnsPicker = ref(false);
const visibleColumns = ref<Record<string, boolean>>({});
const activeFilters = ref<Record<string, string>>({});

const storageKey = computed(() => {
    const baseKey = props.tableKey ?? (typeof window !== 'undefined' ? window.location.pathname : 'table');
    const cols = props.columns.map((col) => col.key).join('|');

    return `datatable:columns:${baseKey}:${cols}`;
});

const defaultVisibleColumns = computed<Record<string, boolean>>(() =>
    props.columns.reduce((acc, col) => {
        acc[col.key] = col.visible !== false;

        return acc;
    }, {} as Record<string, boolean>),
);

const displayedColumns = computed(() =>
    props.columns.filter((col) => visibleColumns.value[col.key] !== false),
);

const filteredRows = computed(() => {
    if (props.filtersConfig.length === 0) {
        return props.rows;
    }

    return props.rows.filter((row) =>
        props.filtersConfig.every((filter) => {
            const filterValue = (activeFilters.value[filter.key] ?? '').trim().toLowerCase();

            if (!filterValue) {
                return true;
            }

            const rowValue = row[filter.columnKey ?? filter.key];

            if (rowValue == null) {
                return false;
            }

            if (filter.type === 'select') {
                return String(rowValue).toLowerCase() === filterValue;
            }

            return String(rowValue).toLowerCase().includes(filterValue);
        }),
    );
});

const sortedRows = computed(() => {
    if (!sortKey.value || !sortDirection.value) {
        return filteredRows.value;
    }

    const key = sortKey.value;
    const direction = sortDirection.value;

    return [...filteredRows.value].sort((a, b) => {
        const aValue = a[key];
        const bValue = b[key];

        if (aValue == null && bValue == null) {
return 0;
}

        if (aValue == null) {
return 1;
}

        if (bValue == null) {
return -1;
}

        const leftNumber = toFiniteNumber(aValue);
        const rightNumber = toFiniteNumber(bValue);

        if (leftNumber !== null && rightNumber !== null) {
            return direction === 'asc' ? leftNumber - rightNumber : rightNumber - leftNumber;
        }

        const leftTimestamp = toTimestamp(aValue);
        const rightTimestamp = toTimestamp(bValue);

        if (leftTimestamp !== null && rightTimestamp !== null) {
            return direction === 'asc' ? leftTimestamp - rightTimestamp : rightTimestamp - leftTimestamp;
        }

        const left = String(aValue);
        const right = String(bValue);

        const result = left.localeCompare(right, undefined, { sensitivity: 'base', numeric: true });

        return direction === 'asc' ? result : -result;
    });
});

const toFiniteNumber = (value: unknown): number | null => {
    if (typeof value === 'number' && Number.isFinite(value)) {
        return value;
    }

    if (typeof value !== 'string') {
        return null;
    }

    const normalized = value.trim();

    if (normalized === '') {
        return null;
    }

    const parsed = Number(normalized);

    return Number.isFinite(parsed) ? parsed : null;
};

const toTimestamp = (value: unknown): number | null => {
    if (value instanceof Date) {
        const time = value.getTime();

        return Number.isFinite(time) ? time : null;
    }

    if (typeof value !== 'string') {
        return null;
    }

    const normalized = value.trim();

    if (normalized === '') {
        return null;
    }

    const parsed = Date.parse(normalized);

    return Number.isNaN(parsed) ? null : parsed;
};

const ariaSortFor = (key: string): 'none' | 'ascending' | 'descending' => {
    if (sortKey.value !== key || !sortDirection.value) {
        return 'none';
    }

    return sortDirection.value === 'asc' ? 'ascending' : 'descending';
};

const toggleSort = (key: string): void => {
    if (sortKey.value !== key) {
        sortKey.value = key;
        sortDirection.value = 'asc';
    } else if (sortDirection.value === 'asc') {
        sortDirection.value = 'desc';
    } else {
        sortKey.value = null;
        sortDirection.value = null;
    }
};

onMounted(() => {
    if (typeof window === 'undefined') {
return;
}

    const saved = localStorage.getItem(storageKey.value);
    visibleColumns.value = saved ? JSON.parse(saved) : defaultVisibleColumns.value;
});

watch(
    visibleColumns,
    (value) => {
        if (typeof window !== 'undefined') {
            localStorage.setItem(storageKey.value, JSON.stringify(value));
        }

        const activeColumns = Object.entries(value)
            .filter(([, isVisible]) => isVisible)
            .map(([key]) => key);

        emit('update:visible-columns', activeColumns);
    },
    { deep: true },
);

watch([sortKey, sortDirection], () => {
    emit('update:sort', {
        key: sortKey.value,
        direction: sortDirection.value,
    });
});

onMounted(() => {
    activeFilters.value = props.filtersConfig.reduce((acc, filter) => {
        acc[filter.key] = '';

        return acc;
    }, {} as Record<string, string>);
});

watch(
    activeFilters,
    (value) => {
        emit('update:filters', value);
    },
    { deep: true },
);

const resetFilters = (): void => {
    activeFilters.value = props.filtersConfig.reduce((acc, filter) => {
        acc[filter.key] = '';

        return acc;
    }, {} as Record<string, string>);
};

onMounted(() => {
    emit('update:filters', {});
});
</script>

<template>
    <div class="relative w-full space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative">
                <AtomButton
                    size="small"
                    title="View"
                    icon="adjustments"
                    variant="outlined"
                    @onClick="openColumnsPicker = !openColumnsPicker"
                />

                <div
                    v-if="openColumnsPicker"
                    class="section-card absolute left-0 z-40 mt-2 w-64 space-y-2 p-3"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        Visible columns
                    </p>
                    <label
                        v-for="col in columns"
                        :key="col.key"
                        class="flex cursor-pointer items-center gap-2 text-sm"
                    >
                        <input
                            v-model="visibleColumns[col.key]"
                            type="checkbox"
                            :disabled="col.hideable === false"
                            class="size-4 rounded border-gray-300"
                        />
                        <span>{{ col.label }}</span>
                    </label>
                </div>
            </div>

            <div v-if="!readOnly" class="flex flex-wrap items-center gap-2">
                <AtomButton
                    v-for="button in buttons"
                    :key="button.key"
                    :title="button.title"
                    :variant="button.variant"
                    :icon="button.icon"
                    size="small"
                    @onClick="button.onClick"
                />
                <slot name="additionalDivsRight" />
                <slot name="exportButton" />
            </div>
        </div>

        <div v-if="filtersConfig.length > 0" class="section-card grid gap-3 p-4 md:grid-cols-[repeat(auto-fit,minmax(220px,1fr))_auto]">
            <div v-for="filter in filtersConfig" :key="filter.key">
                <AtomInput
                    v-if="(filter.type ?? 'text') === 'text'"
                    v-model="activeFilters[filter.key]"
                    :label="filter.label"
                    :placeholder="filter.placeholder ?? ''"
                    input-class="h-9"
                />

                <div v-else class="grid gap-2">
                    <label class="text-sm font-medium">{{ filter.label }}</label>
                    <select
                        v-model="activeFilters[filter.key]"
                        class="h-9 rounded-lg border border-input bg-white px-3 text-sm shadow-xs transition-[color,box-shadow] duration-200 outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                    >
                        <option value="">All</option>
                        <option
                            v-for="option in filter.options ?? []"
                            :key="`${filter.key}-${option.value}`"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex items-end">
                <AtomButton
                    title="Reset filters"
                    variant="outlined"
                    size="small"
                    @onClick="resetFilters"
                />
            </div>
        </div>

        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th
                            v-for="col in displayedColumns"
                            :key="col.key"
                            class="px-4 py-3 text-left"
                            :aria-sort="col.sortable ? ariaSortFor(col.key) : undefined"
                        >
                            <button
                                v-if="col.sortable"
                                type="button"
                                class="inline-flex items-center gap-1 font-semibold"
                                @click="toggleSort(col.key)"
                                :aria-label="`Sort by ${col.label}`"
                            >
                                <span>{{ col.label }}</span>
                                <Eye
                                    v-if="sortKey === col.key && sortDirection === 'asc'"
                                    class="size-3.5 text-muted-foreground"
                                />
                                <EyeOff
                                    v-else-if="sortKey === col.key && sortDirection === 'desc'"
                                    class="size-3.5 text-muted-foreground"
                                />
                                <SlidersHorizontal v-else class="size-3.5 text-muted-foreground" />
                            </button>
                            <span v-else class="font-semibold">{{ col.label }}</span>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="sortedRows.length === 0">
                        <td :colspan="displayedColumns.length" class="px-4 py-10 text-center text-muted-foreground">
                            No data available
                        </td>
                    </tr>

                    <tr v-for="(row, index) in sortedRows" :key="row.id ?? index">
                        <td
                            v-for="col in displayedColumns"
                            :key="`${col.key}-${row.id ?? index}`"
                            class="px-4 py-3 text-sm text-foreground"
                        >
                            <slot
                                v-if="col.slot"
                                :name="col.slot"
                                :row="row"
                            />
                            <span v-else>
                                {{ col.format ? col.format(row) : row[col.key] }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="loading"
            :class="cn('absolute inset-0 flex items-center justify-center rounded-lg bg-white/70 backdrop-blur-[1px]')"
        >
            <div class="space-y-2 text-center">
                <AtomSpinner />
                <p class="text-sm font-medium text-muted-foreground">Loading...</p>
            </div>
        </div>
    </div>
</template>
