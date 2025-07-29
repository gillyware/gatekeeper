import { Button } from '@/components/ui/button';
import { DebouncedInput } from '@/components/ui/debounced-input';
import HeadingSmall from '@/components/ui/heading-small';
import { manageModelText, type ModelEntityTablesText } from '@/lib/lang/en/model/manage';
import { type GatekeeperEntity, type GatekeeperModelEntityDenialMap } from '@/types';
import { type Pagination } from '@/types/api';
import { type GetModelEntitiesPageRequest } from '@/types/api/model';
import { Ban, CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { useEffect, useMemo, type SetStateAction } from 'react';
import { useNavigate } from 'react-router';

interface ModelDeniedEntitiesTableProps<E extends GatekeeperEntity> {
    entity: E;
    data: Pagination<GatekeeperModelEntityDenialMap[E]> | null;
    pageRequest: GetModelEntitiesPageRequest;
    setPageRequest: (value: SetStateAction<GetModelEntitiesPageRequest>) => void;
    refreshPages: () => Promise<void>;
    undenyEntity: (entityName: string) => Promise<boolean>;
    processing: boolean;
    setProcessing: (value: boolean) => void;
    error: string | null;
    canManage: boolean;
    entitySupported: boolean;
    search: string;
    setSearch: (val: string) => void;
}

export default function ModelDeniedEntitiesPage<E extends GatekeeperEntity>({
    entity,
    data,
    pageRequest,
    setPageRequest,
    refreshPages,
    undenyEntity,
    processing,
    setProcessing,
    error,
    canManage,
    entitySupported,
    search,
    setSearch,
}: ModelDeniedEntitiesTableProps<E>) {
    const navigate = useNavigate();
    const language: ModelEntityTablesText = useMemo(() => manageModelText.modelEntityTablesText, []);

    useEffect(() => {
        setSearch(pageRequest.search_term);
    }, [pageRequest.search_term]);

    return (
        <div className="flex flex-col gap-4">
            <HeadingSmall title={language[entity].deniedHeader} />

            <DebouncedInput
                value={search}
                placeholder={language[entity].searchPlaceholder}
                debounceTime={1000}
                setValue={setSearch}
                onDebouncedChange={async (val) => setPageRequest((prev) => ({ ...prev, search_term: val, page: 1 }))}
            />

            {error && <div className="text-center text-sm text-red-500">{error}</div>}

            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <thead className="bg-muted">
                        <tr>
                            <th className="px-2 py-2 text-left font-semibold sm:px-4">{language.nameHeader}</th>
                            <th className="px-2 py-2 text-center font-semibold sm:px-4">{language.grantedByDefaultHeader}</th>
                            <th className="px-2 py-2 text-center font-semibold sm:px-4">{language.statusHeader}</th>
                            {canManage && entitySupported && <th className="px-2 py-2 text-center font-semibold sm:px-4">{language.actionHeader}</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {!data ? (
                            <tr>
                                <td colSpan={canManage && entitySupported ? 4 : 3} className="text-muted-foreground px-2 py-6 text-center sm:px-4">
                                    <Loader className="mx-auto h-4 w-4 animate-spin" />
                                </td>
                            </tr>
                        ) : !data.data.length ? (
                            <tr>
                                <td colSpan={canManage && entitySupported ? 4 : 3} className="text-muted-foreground px-2 py-6 text-center sm:px-4">
                                    {language[entity].deniedEmpty}
                                </td>
                            </tr>
                        ) : (
                            data.data.map((denial) => {
                                const entityModel = denial[entity];

                                return (
                                    <tr key={entityModel.name} className="border-t dark:border-gray-700">
                                        <td className="px-2 py-2 sm:px-4">
                                            <Button className="px-0" variant="link" onClick={() => navigate(`/${entity}s/${entityModel.id}/manage`)}>
                                                {entityModel.name}
                                            </Button>
                                        </td>
                                        <td className="px-2 py-2 text-center sm:px-4">
                                            {entityModel.grant_by_default ? (
                                                <CheckCircle className="mx-auto h-4 w-4 text-green-600 dark:text-green-400" />
                                            ) : (
                                                <Ban className="mx-auto h-4 w-4 text-red-600 dark:text-red-400" />
                                            )}
                                        </td>
                                        <td className="px-2 py-2 text-center sm:px-4">
                                            {entityModel.is_active ? (
                                                <CheckCircle className="mx-auto h-4 w-4 text-green-600 dark:text-green-400" />
                                            ) : (
                                                <PauseCircle className="mx-auto h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                            )}
                                        </td>
                                        {canManage && entitySupported && (
                                            <td className="flex items-center justify-center px-2 py-2 sm:px-4">
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    disabled={processing}
                                                    onClick={async () => {
                                                        setProcessing(true);
                                                        const undenied = await undenyEntity(entityModel.name);
                                                        setProcessing(false);
                                                        if (undenied) await refreshPages();
                                                    }}
                                                >
                                                    {language.undeny}
                                                </Button>
                                            </td>
                                        )}
                                    </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>

            {data && data.total > 0 && (
                <div className="flex justify-end gap-2 pt-2">
                    <Button
                        size="sm"
                        variant="outline"
                        disabled={data.current_page === 1}
                        onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page - 1 }))}
                    >
                        {language.previous}
                    </Button>

                    <span className="text-sm">{language.pagination(data.from, data.to, data.total)}</span>

                    <Button
                        size="sm"
                        variant="outline"
                        disabled={data.current_page === data.last_page}
                        onClick={() => setPageRequest((prev) => ({ ...prev, page: prev.page + 1 }))}
                    >
                        {language.next}
                    </Button>
                </div>
            )}
        </div>
    );
}
