// import { DebouncedInput } from '@/components/ui/debounced-input';
// import { useApi } from '@/lib/api';
// import { cn } from '@/lib/utils';
// import { Pagination } from '@/types/api';
// import { ConfiguredModelMetadata, ConfiguredModelSearchByPermissionResult } from '@/types/api/model';
// import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@components/ui/select';
// import { Loader } from 'lucide-react';
// import { useEffect, useMemo, useState } from 'react';
// import { useNavigate } from 'react-router';

// export default function ModelsSearchByPermission() {
//     const api = useApi();
//     const navigate = useNavigate();

//     const [configuredModels, setConfiguredModels] = useState<ConfiguredModelMetadata[]>([]);
//     const [configuredModelLabel, setConfiguredModelLabel] = useState<string>('');
//     const [pageNumber, setPageNumber] = useState<number>(1);
//     const [permissionNameSearchTerm, setPermissionNameSearchTerm] = useState<string>('');
//     const [modelSearchTerm, setModelSearchTerm] = useState<string>('');
//     const [modelSearchResults, setModelSearchResults] = useState<ConfiguredModelSearchByPermissionResult[]>([]);
//     const [loading, setLoading] = useState(true);
//     const [error, setError] = useState<string | null>(null);

//     const configuredModel = useMemo(() => {
//         if (!configuredModelLabel) {
//             return null;
//         }

//         return configuredModels.find((m) => m.model_label === configuredModelLabel) || null;
//     }, [configuredModelLabel]) as ConfiguredModelMetadata | null;

//     const numberOfColumns = useMemo(() => {
//         if (!configuredModelLabel) {
//             return 0;
//         }

//         const searchableColumnCount = Object.keys(configuredModel?.searchable || {}).length;
//         return searchableColumnCount > 0 ? searchableColumnCount + 1 : 0;
//     }, [configuredModel]) as number;

//     const searchPlaceholder = useMemo(() => {
//         if (!configuredModel) {
//             return undefined;
//         }

//         const searchableLabels = Object.values(configuredModel.searchable || {});

//         if (searchableLabels.length === 0) {
//             return undefined;
//         }

//         return `Search by ${searchableLabels.join(', ')}`;
//     }, [configuredModel]);

//     useEffect(() => {
//         const fetchModelTypes = async () => {
//             const response = await api.getConfiguredModels();

//             if (response.status >= 400) {
//                 setError(response.errors?.general || 'Failed to fetch configured models.');
//                 return;
//             }

//             const configured = response.data as ConfiguredModelMetadata[];
//             setConfiguredModels(configured);
//             if (configured.length > 0) {
//                 setConfiguredModelLabel(configured[0].model_label);
//             }
//         };

//         fetchModelTypes();
//     }, []);

//     useEffect(() => {
//         if (configuredModelLabel) {
//             searchPermissionAssignments('', '');
//         }
//     }, [configuredModelLabel]);

//     const searchPermissionAssignments = async (permissionTerm: string, modelTerm: string) => {
//         setLoading(true);
//         setError(null);

//         const response = await api.searchModelsByPermission({
//             page: pageNumber,
//             model_label: configuredModel?.model_label as string,
//             search_term: modelTerm,
//             permission_name_search_term: permissionTerm,
//         });

//         console.log(response);

//         if (response.status >= 400) {
//             setError(response.errors?.general || 'Failed to search models by permission.');
//             setModelSearchResults([]);
//             setLoading(false);
//             return;
//         }

//         const results = response.data as Pagination<ConfiguredModelSearchByPermissionResult>;
//         setModelSearchResults(results.data as ConfiguredModelSearchByPermissionResult[]);
//         setLoading(false);
//     };

//     return (
//         <div className="flex w-full flex-col gap-8">
//             <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-center">
//                 <Select value={configuredModelLabel} onValueChange={setConfiguredModelLabel}>
//                     <SelectTrigger className="w-48">
//                         <SelectValue placeholder="Select model" />
//                     </SelectTrigger>
//                     <SelectContent>
//                         {configuredModels.map((m) => (
//                             <SelectItem key={m.model_label} value={m.model_label}>
//                                 {m.model_label}
//                             </SelectItem>
//                         ))}
//                     </SelectContent>
//                 </Select>

//                 <DebouncedInput
//                     value={modelSearchTerm}
//                     placeholder={searchPlaceholder}
//                     debounceTime={1000}
//                     setValue={setModelSearchTerm}
//                     onDebouncedChange={(value) => searchPermissionAssignments(permissionNameSearchTerm, value)}
//                 />

//                 <DebouncedInput
//                     value={permissionNameSearchTerm}
//                     placeholder={'Search by permission name'}
//                     debounceTime={1000}
//                     setValue={setPermissionNameSearchTerm}
//                     onDebouncedChange={(value) => searchPermissionAssignments(value, modelSearchTerm)}
//                 />
//             </div>

//             <div className="overflow-auto rounded-lg border dark:border-gray-700">
//                 <table className="w-full text-sm">
//                     <thead className="bg-muted">
//                         <tr>
//                             <th className="px-4 py-2 text-left font-semibold">Permission Name</th>
//                             {Object.values(configuredModel?.displayable || {}).map((label) => (
//                                 <th key={label} className="px-4 py-2 text-left font-semibold">
//                                     {configuredModelLabel} {label}
//                                 </th>
//                             ))}
//                         </tr>
//                     </thead>
//                     <tbody>
//                         {loading ? (
//                             <tr>
//                                 <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
//                                     <div className="inline-flex items-center gap-2">
//                                         <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
//                                     </div>
//                                 </td>
//                             </tr>
//                         ) : error ? (
//                             <tr>
//                                 <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
//                                     {error}
//                                 </td>
//                             </tr>
//                         ) : modelSearchResults.length === 0 ? (
//                             <tr>
//                                 <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
//                                     No results found.
//                                 </td>
//                             </tr>
//                         ) : (
//                             modelSearchResults.map((modelSearchResult) => (
//                                 <tr
//                                     key={modelSearchResult.model_pk}
//                                     role="button"
//                                     tabIndex={0}
//                                     onClick={() => {
//                                         navigate(`/models/${modelSearchResult.model_label}/${modelSearchResult.model_pk}`);
//                                     }}
//                                     onKeyDown={(e) => {
//                                         if (e.key === 'Enter') {
//                                             navigate(`/models/${modelSearchResult.model_label}/${modelSearchResult.model_pk}`);
//                                         }
//                                     }}
//                                     className={cn(
//                                         'cursor-pointer border-t transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800',
//                                     )}
//                                 >
//                                     <td className="px-4 py-2">{modelSearchResult.permission.name || 'N/A'}</td>
//                                     {Object.keys(modelSearchResult.displayable).map((displayableField) => (
//                                         <td key={displayableField} className="px-4 py-2">
//                                             {modelSearchResult.display[displayableField] || 'N/A'}
//                                         </td>
//                                     ))}
//                                 </tr>
//                             ))
//                         )}
//                     </tbody>
//                 </table>
//             </div>

//             <div className="overflow-auto rounded-lg border dark:border-gray-700">
//                 <table className="w-full text-sm">
//                     <thead className="bg-muted">
//                         <tr>
//                             <th className="px-4 py-2 text-left font-semibold">Permission Name</th>
//                             {Object.values(configuredModel?.displayable || {}).map((label) => (
//                                 <th key={label} className="px-4 py-2 text-left font-semibold">
//                                     {configuredModelLabel} {label}
//                                 </th>
//                             ))}
//                         </tr>
//                     </thead>
//                     <tbody>
//                         {loading ? (
//                             <tr>
//                                 <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
//                                     <div className="inline-flex items-center gap-2">
//                                         <Loader className="mx-auto h-4 w-4 animate-spin text-gray-500 dark:text-gray-400" />
//                                     </div>
//                                 </td>
//                             </tr>
//                         ) : error ? (
//                             <tr>
//                                 <td colSpan={numberOfColumns} className="px-4 py-6 text-center text-red-500">
//                                     {error}
//                                 </td>
//                             </tr>
//                         ) : modelSearchResults.length === 0 ? (
//                             <tr>
//                                 <td colSpan={numberOfColumns} className="text-muted-foreground px-4 py-6 text-center">
//                                     No results found.
//                                 </td>
//                             </tr>
//                         ) : (
//                             modelSearchResults.map((modelSearchResult) => (
//                                 <tr
//                                     key={modelSearchResult.model_pk}
//                                     role="button"
//                                     tabIndex={0}
//                                     onClick={() => {
//                                         navigate(`/models/${modelSearchResult.model_label}/${modelSearchResult.model_pk}`);
//                                     }}
//                                     onKeyDown={(e) => {
//                                         if (e.key === 'Enter') {
//                                             navigate(`/models/${modelSearchResult.model_label}/${modelSearchResult.model_pk}`);
//                                         }
//                                     }}
//                                     className={cn(
//                                         'cursor-pointer border-t transition-colors hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800',
//                                     )}
//                                 >
//                                     <td className="px-4 py-2">{modelSearchResult.permission.name || 'N/A'}</td>
//                                     {Object.keys(modelSearchResult.displayable).map((displayableField) => (
//                                         <td key={displayableField} className="px-4 py-2">
//                                             {modelSearchResult.display[displayableField] || 'N/A'}
//                                         </td>
//                                     ))}
//                                 </tr>
//                             ))
//                         )}
//                     </tbody>
//                 </table>
//             </div>
//         </div>
//     );
// }
