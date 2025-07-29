import { Button } from '@/components/ui/button';
import { DebouncedInput } from '@/components/ui/debounced-input';
import HeadingSmall from '@/components/ui/heading-small';
import { manageModelText, type ModelEntityTablesText } from '@/lib/lang/en/model/manage';
import { type GatekeeperEntity, type GatekeeperModelEntityAssignmentMap } from '@/types';
import { type Pagination } from '@/types/api';
import { type GetModelEntitiesPageRequest } from '@/types/api/model';
import { CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { type SetStateAction, useMemo } from 'react';
import { useNavigate } from 'react-router';

interface ModelEntityAssignmentsTableProps<E extends GatekeeperEntity> {
    entity: E;
    data: Pagination<GatekeeperModelEntityAssignmentMap[E]> | null;
    pageRequest: GetModelEntitiesPageRequest;
    setPageRequest: (value: SetStateAction<GetModelEntitiesPageRequest>) => void;
    refreshPages: () => Promise<void>;
    onEntityAction: (entityName: string) => Promise<boolean>;
    processing: boolean;
    setProcessing: (value: boolean) => void;
    error: string | null;
    canManage: boolean;
    search: string;
    setSearch: (val: string) => void;
}

export default function ModelEntityAssignmentsTable<E extends GatekeeperEntity>({
    entity,
    data,
    pageRequest,
    setPageRequest,
    refreshPages,
    onEntityAction,
    processing,
    setProcessing,
    error,
    canManage,
    search,
    setSearch,
}: ModelEntityAssignmentsTableProps<E>) {
    const navigate = useNavigate();
    const language: ModelEntityTablesText = useMemo(() => manageModelText.modelEntityTablesText, []);

    return (
        <div className="flex flex-col gap-4">
            <HeadingSmall title={language[entity].assignedHeader} />

            <DebouncedInput
                value={search}
                placeholder={language[entity].searchPlaceholder}
                debounceTime={1000}
                setValue={setSearch}
                onDebouncedChange={async (val) => {
                    setPageRequest((prev) => ({ ...prev, search_term: val, page: 1 }));
                }}
            />

            {error && <div className="text-center text-sm text-red-500">{error}</div>}

            <div className="overflow-auto rounded-lg border dark:border-gray-700">
                <table className="w-full text-sm">
                    <thead className="bg-muted">
                        <tr>
                            <th className="px-2 py-2 text-left font-semibold sm:px-4">{language.nameHeader}</th>
                            <th className="px-2 py-2 text-center font-semibold sm:px-4">{language.statusHeader}</th>
                            <th className="hidden px-2 py-2 text-center font-semibold sm:table-cell sm:px-4">{language.assignedDateTimeHeader}</th>
                            {canManage && <th className="px-2 py-2 text-center font-semibold sm:px-4">{language.actionHeader}</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {!data ? (
                            <tr>
                                <td colSpan={canManage ? 4 : 3} className="text-muted-foreground px-2 py-6 text-center sm:px-4">
                                    <Loader className="mx-auto h-4 w-4 animate-spin" />
                                </td>
                            </tr>
                        ) : !data.data.length ? (
                            <tr>
                                <td colSpan={canManage ? 4 : 3} className="text-muted-foreground px-2 py-6 text-center sm:px-4">
                                    {language[entity].assignedEmpty}
                                </td>
                            </tr>
                        ) : (
                            data.data.map((assignment) => {
                                const entityData = assignment[entity];

                                return (
                                    <tr key={entityData.name} className="border-t dark:border-gray-700">
                                        <td className="px-2 py-2 sm:px-4">
                                            <Button className="px-0" variant="link" onClick={() => navigate(`/${entity}s/${entityData.id}/manage`)}>
                                                {entityData.name}
                                            </Button>
                                        </td>
                                        <td className="px-2 py-2 text-center sm:px-4">
                                            {entityData.is_active ? (
                                                <CheckCircle className="mx-auto h-4 w-4 text-green-600 dark:text-green-400" />
                                            ) : (
                                                <PauseCircle className="mx-auto h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                            )}
                                        </td>
                                        <td className="hidden px-2 py-2 text-center sm:table-cell sm:px-4">{assignment.assigned_at || ''}</td>
                                        {canManage && (
                                            <td className="flex items-center justify-center px-2 py-2 sm:px-4">
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    disabled={processing}
                                                    onClick={async () => {
                                                        setProcessing(true);
                                                        const unassigned = await onEntityAction(entityData.name);
                                                        setProcessing(false);
                                                        if (unassigned) refreshPages();
                                                    }}
                                                >
                                                    {language.unassign}
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
                        onClick={() => setPageRequest((prev) => ({ ...prev, page: pageRequest.page - 1 }))}
                    >
                        {language.previous}
                    </Button>

                    <span className="text-sm">{language.pagination(data.from, data.to, data.total)}</span>

                    <Button
                        size="sm"
                        variant="outline"
                        disabled={data.current_page === data.last_page}
                        onClick={() => setPageRequest((prev) => ({ ...prev, page: pageRequest.page + 1 }))}
                    >
                        {language.next}
                    </Button>
                </div>
            )}
        </div>
    );
}
