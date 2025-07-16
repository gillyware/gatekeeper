export interface ModelIndexText {
    modelTableText: ModelTableText;
}

export interface ModelTableText {
    dropdownPlaceholder: string;
    searchInputPlaceholder: (fields: string[]) => string;
    empty: string;
    errorFallback: string;
}

export const modelIndexText: ModelIndexText = {
    modelTableText: {
        dropdownPlaceholder: 'Select model',
        searchInputPlaceholder: (fields: string[]) => (fields.length === 0 ? 'Search...' : `Search by ${fields.join(', ')}`),
        empty: 'No results found.',
        errorFallback: 'Failed to load models.',
    },
};
