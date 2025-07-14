import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import { fetchConfiguredModels } from '@/lib/models';
import { cn } from '@/lib/utils';
import { type ConfiguredModelMetadata, type ConfiguredModelSearchResult } from '@/types/api/model';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select';
import { Loader } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router';

export default function ModelsSearch() {
    const { user } = useGatekeeper();
    const api = useApi();
    const navigate = useNavigate();

    const [configuredModels, setConfiguredModels] = useState<ConfiguredModelMetadata[]>([]);
    const [configuredModelLabel, setConfiguredModelLabel] = useState<string>('');
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [modelSearchResults, setModelSearchResults] = useState<ConfiguredModelSearchResult[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const configuredModel = useMemo(() => {
        if (!configuredModelLabel) {
            return null;
        }

        return configuredModels.find((m) => m.model_label === configuredModelLabel) || null;
    }, [configuredModelLabel]) as ConfiguredModelMetadata | null;

    const numberOfColumns = useMemo(() => {
        if (!configuredModelLabel) {
            return 0;
        }

        return (configuredModel?.searchable || []).length;
    }, [configuredModel]) as number;

    const searchPlaceholder = useMemo(() => {
        if (!configuredModel) {
            return 'Search...';
        }

        const searchableLabels = (configuredModel?.searchable || []).map((x) => x.label);

        if (searchableLabels.length === 0) {
            return 'Search...';
        }

        return `Search by ${searchableLabels.join(', ')}`;
    }, [configuredModel]);

    useEffect(() => {
        fetchConfiguredModels(
            api,
            (models) => {
                setConfiguredModels(models);
                if (models.length > 0) {
                    setConfiguredModelLabel(models[0].model_label);
                }
            },
            setLoading,
            setError,
        );
    }, []);

    useEffect(() => {
        if (configuredModelLabel) {
            searchModels('');
        }
    }, [configuredModelLabel]);

    const searchModels = async (term: string) => {
        setLoading(true);
        setError(null);

        const response = await api.searchModels({ model_label: configuredModel?.model_label as string, search_term: term });

        if (response.status >= 400) {
            setError(response.errors?.general || 'Failed to search models.');
            setModelSearchResults([]);
            setLoading(false);
            return;
        }

        const results = response.data as ConfiguredModelSearchResult[];
        setModelSearchResults(results);
        setLoading(false);
    };

    return (
        <div className="w-full">
            <div className="mb-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-center">
                <Select value={configuredModelLabel} onValueChange={setConfiguredModelLabel}>
                    <SelectTrigger className="w-48">
                        <SelectValue placeholder="Select model" />
                    </SelectTrigger>
                    <SelectContent>
                        {configuredModels.map((m) => (
                            <SelectItem key={m.model_label} value={m.model_label}>
                                {m.model_label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <DebouncedInput
                    value={searchTerm}
                    placeholder={searchPlaceholder}
                    debounceTime={1000}
                    setValue={setSearchTerm}
                    onDebouncedChange={searchModels}
                />
            </div>

            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <thead className="bg-muted">
                        <tr>
                            {(configuredModel?.displayable || []).map((x) => (
                                <th key={x.label} className="px-4 py-2 text-left font-semibold">
                                    {x.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                            <tr>
                                <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                    <div className="inline-flex items-center gap-2">
                                        <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                    </div>
                                </td>
                            </tr>
                        ) : error ? (
                            <tr>
                                <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
                                    {error}
                                </td>
                            </tr>
                        ) : modelSearchResults.length === 0 ? (
                            <tr>
                                <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                    No results found.
                                </td>
                            </tr>
                        ) : (
                            modelSearchResults.map((modelSearchResult) => (
                                <tr
                                    key={modelSearchResult.model_pk}
                                    role="button"
                                    tabIndex={0}
                                    onClick={() => {
                                        navigate(`/models/${modelSearchResult.model_label}/${modelSearchResult.model_pk}`);
                                    }}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            navigate(`/models/${modelSearchResult.model_label}/${modelSearchResult.model_pk}`);
                                        }
                                    }}
                                    className={cn(
                                        'cursor-pointer border-t transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800',
                                    )}
                                >
                                    {modelSearchResult.displayable.map((x) => (
                                        <td key={x.column} className="px-4 py-2">
                                            {modelSearchResult.display[x.column] || 'N/A'}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
