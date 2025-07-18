import { DebouncedInput } from '@/components/ui/debounced-input';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { manageModelText, type ModelEntityTablesText } from '@/lib/lang/en/model/manage';
import { type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types';
import { type Pagination } from '@/types/api';
import { type EntitySupported, type GetModelEntitiesPageRequest } from '@/types/api/model';
import { Button } from '@components/ui/button';
import HeadingSmall from '@components/ui/heading-small';
import { CheckCircle, Loader, PauseCircle } from 'lucide-react';
import { useMemo, type SetStateAction } from 'react';
import { useNavigate } from 'react-router';

interface ModelUnassignedEntitiesTableProps<E extends GatekeeperEntity> {
    entity: GatekeeperEntity;
    modelUnassignedEntities: Pagination<GatekeeperEntityModelMap[E]> | null;
    searchTerm: string;
    pageRequest: GetModelEntitiesPageRequest;
    setSearchTerm: (value: SetStateAction<string>) => void;
    setPageRequest: (value: SetStateAction<GetModelEntitiesPageRequest>) => void;
    refreshPages: () => Promise<void>;
    assignEntityToModel: (entityName: string) => Promise<void>;
    loadingModelUnassignedEntities: boolean;
    processingEntityAssignment: boolean;
    errorLoadingModelUnassignedEntities: string | null;
    errorAssigningEntity: string | null;
    numberOfColumns: number;
    entitySupported: EntitySupported;
}

export default function ModelUnassignedEntitiesTable<E extends GatekeeperEntity>({
    entity,
    modelUnassignedEntities,
    searchTerm,
    pageRequest,
    setSearchTerm,
    setPageRequest,
    refreshPages,
    assignEntityToModel,
    loadingModelUnassignedEntities,
    processingEntityAssignment,
    errorLoadingModelUnassignedEntities,
    errorAssigningEntity,
    numberOfColumns,
    entitySupported,
}: ModelUnassignedEntitiesTableProps<E>) {
    const { user } = useGatekeeper();
    const navigate = useNavigate();
    const language: ModelEntityTablesText = useMemo(() => manageModelText.modelEntityTablesText, []);

    return (
        <div className="flex w-full flex-col gap-8">
            <div className="flex flex-col gap-4">
                <HeadingSmall title={language[entity].unassignedHeader} />

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

                {errorAssigningEntity && <div className="w-full px-4 py-6 text-center text-red-500">{errorAssigningEntity}</div>}

                <div className="overflow-auto rounded-lg border dark:border-gray-700">
                    <table className="w-full text-sm">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left font-semibold">{language[entity].nameHeader}</th>
                                <th className="px-4 py-2 text-center font-semibold">{language[entity].statusHeader}</th>
                                {user.permissions.can_manage && entitySupported.supported && (
                                    <th className="px-6 py-2 text-left font-semibold">{language.actionHeader}</th>
                                )}
                            </tr>
                        </thead>
                        <tbody>
                            {loadingModelUnassignedEntities ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        <div className="inline-flex items-center gap-2">
                                            <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
                                        </div>
                                    </td>
                                </tr>
                            ) : errorLoadingModelUnassignedEntities ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
                                        {errorLoadingModelUnassignedEntities}
                                    </td>
                                </tr>
                            ) : !modelUnassignedEntities?.data.length ? (
                                <tr>
                                    <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
                                        {language[entity].unassignedEmpty}
                                    </td>
                                </tr>
                            ) : (
                                modelUnassignedEntities.data.map((entityModel) => (
                                    <tr key={entityModel.name} className="border-t transition-colors dark:border-gray-700">
                                        <td className="px-4 py-2">
                                            <Button
                                                className="px-0"
                                                variant="link"
                                                onClick={() => {
                                                    navigate(`/${entity}s/${entityModel.id}/manage`);
                                                }}
                                            >
                                                {entityModel.name}
                                            </Button>
                                        </td>
                                        <td className="flex items-center justify-center px-4 py-2">
                                            <div className="flex h-8 w-full items-center justify-center">
                                                {entityModel.is_active ? (
                                                    <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                ) : (
                                                    <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                                )}
                                            </div>
                                        </td>
                                        {user.permissions.can_manage && entitySupported.supported && (
                                            <td className="px-4 py-2">
                                                <Button
                                                    className="px-2"
                                                    variant="ghost"
                                                    size="sm"
                                                    disabled={processingEntityAssignment}
                                                    onClick={async () => {
                                                        await assignEntityToModel(entityModel.name);
                                                        refreshPages();
                                                    }}
                                                >
                                                    {language.assign}
                                                </Button>
                                            </td>
                                        )}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {modelUnassignedEntities && modelUnassignedEntities.total > 0 && (
                    <div className="flex w-full items-center justify-end gap-2 pt-2">
                        <Button
                            size="sm"
                            variant="outline"
                            disabled={modelUnassignedEntities.current_page === 1}
                            onClick={() => {
                                setPageRequest((prev) => ({ ...prev, page: pageRequest.page - 1 }));
                            }}
                        >
                            {language.previous}
                        </Button>

                        <span className="text-sm">
                            {language.pagination(modelUnassignedEntities.from, modelUnassignedEntities.to, modelUnassignedEntities.total)}
                        </span>

                        <Button
                            size="sm"
                            variant="outline"
                            disabled={modelUnassignedEntities.current_page === modelUnassignedEntities.last_page}
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
