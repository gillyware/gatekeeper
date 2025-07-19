import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { useIsMobile } from '@/hooks/use-mobile';
import { manageModelText, type ModelEntityTablesText } from '@/lib/lang/en/model/manage';
import { cn } from '@/lib/utils';
import { type GatekeeperEntity, type GatekeeperModelEntityAssignmentMap } from '@/types';
import { type Pagination } from '@/types/api';
import { type GetModelEntitiesPageRequest } from '@/types/api/model';
import { Button } from '@components/ui/button';
import HeadingSmall from '@components/ui/heading-small';
import { CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { type SetStateAction, useMemo } from 'react';
import { useNavigate } from 'react-router';

interface ModelEntityAssignmentsTableProps<E extends GatekeeperEntity> {
    entity: GatekeeperEntity;
    modelEntityAssignments: Pagination<GatekeeperModelEntityAssignmentMap[E]> | null;
    searchTerm: string;
    pageRequest: GetModelEntitiesPageRequest;
    setSearchTerm: (value: SetStateAction<string>) => void;
    setPageRequest: (value: SetStateAction<GetModelEntitiesPageRequest>) => void;
    refreshPages: () => Promise<void>;
    revokeEntityFromModel: (entityName: string) => Promise<void>;
    loadingModelEntityAssignments: boolean;
    processingEntityRevocation: boolean;
    errorLoadingModelEntityAssignments: string | null;
    errorRevokingEntity: string | null;
    numberOfColumns: number;
}

export default function ModelEntityAssignmentsTable<E extends GatekeeperEntity>({
    entity,
    modelEntityAssignments,
    searchTerm,
    pageRequest,
    setSearchTerm,
    setPageRequest,
    refreshPages,
    revokeEntityFromModel,
    loadingModelEntityAssignments,
    processingEntityRevocation,
    errorLoadingModelEntityAssignments,
    errorRevokingEntity,
    numberOfColumns,
}: ModelEntityAssignmentsTableProps<E>) {
    const isMobile = useIsMobile();
    const { user } = useGatekeeper();
    const navigate = useNavigate();
    const language: ModelEntityTablesText = useMemo(() => manageModelText.modelEntityTablesText, []);

    return (
        <div className="flex w-full flex-col gap-8">
            <div className="flex flex-col gap-4">
                <HeadingSmall title={language[entity].assignedHeader} />

                <DebouncedInput
                    value={searchTerm}
                    placeholder={language[entity].searchPlaceholder}
                    debounceTime={1000}
                    setValue={setSearchTerm}
                    onDebouncedChange={async (value: string) => {
                        setPageRequest((prev) => ({
                            ...prev,
                            page: 1,
                            search_term: value,
                        }));
                    }}
                />

                {errorRevokingEntity && <div className="w-full px-4 py-6 text-center text-red-500">{errorRevokingEntity}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">{language[entity].nameHeader}</th>
                                <th className="px-4 py-2 text-center font-semibold">{language[entity].statusHeader}</th>
                                <th className={cn('px-4 py-2 text-center font-semibold', isMobile && 'hidden')}>{language.assignedDateTimeHeader}</th>
                                {user.permissions.can_manage && <th className="px-6 py-2 text-left font-semibold">{language.actionHeader}</th>}
                            </tr>
                        </thead>
                        <tbody>
                            {loadingModelEntityAssignments ? (
                                <tr>
                                    <td
                                        colSpan={isMobile ? numberOfColumns : numberOfColumns + 1}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        <div className="inline-flex items-center gap-2">
                                            <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                        </div>
                                    </td>
                                </tr>
                            ) : errorLoadingModelEntityAssignments ? (
                                <tr>
                                    <td colSpan={isMobile ? numberOfColumns : numberOfColumns + 1} className="px-4 py-6 text-center text-red-500">
                                        {errorLoadingModelEntityAssignments}
                                    </td>
                                </tr>
                            ) : !modelEntityAssignments?.data.length ? (
                                <tr>
                                    <td
                                        colSpan={isMobile ? numberOfColumns : numberOfColumns + 1}
                                        className="text-muted-foreground px-4 py-6 text-center"
                                    >
                                        {language[entity].assignedEmpty}
                                    </td>
                                </tr>
                            ) : (
                                modelEntityAssignments.data.map((assignment) => (
                                    <tr key={assignment[entity].name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">
                                            <Button
                                                className="px-0"
                                                variant="link"
                                                onClick={() => {
                                                    navigate(`/${entity}s/${assignment[entity].id}/manage`);
                                                }}
                                            >
                                                {assignment[entity].name}
                                            </Button>
                                        </td>
                                        <td className="px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {assignment[entity].is_active ? (
                                                    <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                ) : (
                                                    <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                                )}
                                            </div>
                                        </td>
                                        <td className={cn('px-4 py-2 text-center font-semibold', isMobile && 'hidden')}>
                                            {assignment.assigned_at || ''}
                                        </td>
                                        {user.permissions.can_manage && (
                                            <td className="px-4 py-2">
                                                <Button
                                                    className="px-2"
                                                    variant="ghost"
                                                    size="sm"
                                                    disabled={processingEntityRevocation}
                                                    onClick={async () => {
                                                        await revokeEntityFromModel(assignment[entity].name);
                                                        refreshPages();
                                                    }}
                                                >
                                                    {language.revoke}
                                                </Button>
                                            </td>
                                        )}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {modelEntityAssignments && modelEntityAssignments.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={modelEntityAssignments.current_page === 1}
                            onClick={() => {
                                setPageRequest((prev) => ({ ...prev, page: pageRequest.page - 1 }));
                            }}
                        >
                            {language.previous}
                        </Button>

                        <span className="text-sm">
                            {language.pagination(modelEntityAssignments.from, modelEntityAssignments.to, modelEntityAssignments.total)}
                        </span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={modelEntityAssignments.current_page === modelEntityAssignments.last_page}
                            onClick={() => {
                                setPageRequest((prev) => ({ ...prev, page: pageRequest.page + 1 }));
                            }}
                        >
                            {language.next}
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
}
