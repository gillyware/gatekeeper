import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import { modelIndexText, type ModelTableText } from '@/lib/lang/en/model';
import { getModels } from '@/lib/models';
import { cn } from '@/lib/utils';
import { type GatekeeperConfig } from '@/types';
import { type ConfiguredModelMetadata, type ConfiguredModelSearchResult, type ModelPageRequest } from '@/types/api/model';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select';
import { Loader } from 'lucide-react';
import { type SetStateAction, useEffect, useMemo, useState } from 'react';
import { type NavigateFunction, useNavigate } from 'react-router';

interface ModelsTableFilterProps {
    config: GatekeeperConfig;
    pageRequest: ModelPageRequest;
    setPageRequest: (request: SetStateAction<ModelPageRequest>) => void;
    searchPlaceholder: string;
    searchTerm: string;
    setSearchTerm: (value: SetStateAction<string>) => void;
    onDebouncedChange: (value: string) => Promise<void>;
    language: ModelTableText;
}

interface ModelsTableHeaderProps {
    modelMetadata: ConfiguredModelMetadata | null;
}

interface ModelsTableRowProps {
    model: ConfiguredModelSearchResult;
    navigate: NavigateFunction;
}

export default function ModelsTable() {
    const { config } = useGatekeeper();
    const api = useApi();
    const navigate = useNavigate();

    const initialPageRequest: ModelPageRequest = {
        model_label: config.models.length > 0 ? config.models[0].model_label : '',
        search_term: '',
    };

    const [models, setModels] = useState<ConfiguredModelSearchResult[]>([]);
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [pageRequest, setPageRequest] = useState<ModelPageRequest>(initialPageRequest);

    const [loadingModels, setLoadingModels] = useState<boolean>(true);
    const [errorLoadingModels, setErrorLoadingModels] = useState<string | null>(null);

    const language: ModelTableText = modelIndexText.modelTableText;

    const modelMetadata: ConfiguredModelMetadata | null = useMemo(
        () => config.models.find((m) => m.model_label === pageRequest.model_label) || null,
        [pageRequest],
    );
    const numberOfColumns: number = useMemo(() => modelMetadata?.searchable.length || 0, [modelMetadata]);
    const searchPlaceholder = useMemo(() => language.searchInputPlaceholder((modelMetadata?.searchable || []).map((m) => m.label)), [modelMetadata]);

    useEffect(() => {
        getModels(api, pageRequest, setModels, setLoadingModels, setErrorLoadingModels);
    }, [pageRequest]);

    return (
        <div className="w-full">
            <ModelsTableFilter
                config={config}
                pageRequest={pageRequest}
                setPageRequest={setPageRequest}
                searchPlaceholder={searchPlaceholder}
                searchTerm={searchTerm}
                setSearchTerm={setSearchTerm}
                onDebouncedChange={async (value: string) => {
                    setSearchTerm(value);
                    setPageRequest((prev) => ({ ...prev, search_term: value }));
                }}
                language={language}
            />

            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <ModelsTableHeader modelMetadata={modelMetadata} />
                    <tbody>
                        {loadingModels ? (
                            <tr>
                                <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                    <div className="inline-flex items-center gap-2">
                                        <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                    </div>
                                </td>
                            </tr>
                        ) : errorLoadingModels ? (
                            <tr>
                                <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
                                    {errorLoadingModels || language.errorFallback}
                                </td>
                            </tr>
                        ) : models.length === 0 ? (
                            <tr>
                                <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                    {language.empty}
                                </td>
                            </tr>
                        ) : (
                            models.map((model) => <ModelsTableRow key={model.model_pk} model={model} navigate={navigate} />)
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function ModelsTableFilter({
    config,
    pageRequest,
    setPageRequest,
    searchPlaceholder,
    searchTerm,
    setSearchTerm,
    onDebouncedChange,
    language,
}: ModelsTableFilterProps) {
    return (
        <div className="mb-4 flex flex-col gap-4 md:flex-row md:items-end md:justify-center">
            <Select
                value={pageRequest.model_label}
                onValueChange={(value: string) => {
                    setSearchTerm('');
                    setPageRequest({ model_label: value, search_term: '' });
                }}
            >
                <SelectTrigger className="w-48">
                    <SelectValue placeholder={language.dropdownPlaceholder} />
                </SelectTrigger>
                <SelectContent>
                    {config.models.map((m) => (
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
                onDebouncedChange={onDebouncedChange}
            />
        </div>
    );
}

function ModelsTableHeader({ modelMetadata }: ModelsTableHeaderProps) {
    return (
        <thead className="bg-muted">
            <tr>
                {(modelMetadata?.displayable || []).map((x) => (
                    <th key={x.label} className="px-4 py-2 text-left font-semibold">
                        {x.label}
                    </th>
                ))}
            </tr>
        </thead>
    );
}

function ModelsTableRow({ model, navigate }: ModelsTableRowProps) {
    return (
        <tr
            role="button"
            tabIndex={0}
            onClick={() => {
                navigate(`/models/${model.model_label}/${model.model_pk}`);
            }}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    navigate(`/models/${model.model_label}/${model.model_pk}`);
                }
            }}
            className={cn('cursor-pointer border-t transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800')}
        >
            {model.displayable.map((x) => (
                <td key={x.column} className="px-4 py-2">
                    {model.display[x.column] || ''}
                </td>
            ))}
        </tr>
    );
}
